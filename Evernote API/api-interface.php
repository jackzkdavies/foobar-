<?php
// Author Jack Z K Davies. Jack.z.k.davies@gmail.com.
// *** READE ME ***
// This code is an *outline only* due to NDA surrounding the full orignal code, 
// and is intended to give an overview of the process behind interacting with the Evernote API, and shows a limited number of end point examples
// Due to this some classes used are not defined here and this code is untested in this exact format
//

include_once(__DIR__.'/../../../inc/registration/membersite_config.php');
include_once(__DIR__.'/protected/token-health.php');

use EDAM\UserStore\UserStoreClient;

use EDAM\NoteStore\NoteStoreClient;
use EDAM\NoteStore\NoteFilter;
use EDAM\NoteStore\NotesMetadataResultSpec;

use EDAM\Error\EDAMSystemException,
	EDAM\Error\EDAMUserException,
	EDAM\Error\EDAMErrorCode,
	EDAM\Error\EDAMNotFoundException;

use Thrift\Exception\TTransportException;

use Thrift\Protocol\TBinaryProtocol;

use Thrift\Transport\THttpClient;

use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Notebook, EDAM\Types\Tag, EDAM\Types\LinkedNotebook, EDAM\Types\Resource, EDAM\Types\ResourceAttributes;

date_default_timezone_set("UTC"); 

class EvernoteAPIInterface {

	protected $id_user;

	protected $token;

	protected $verbose;

	protected $errorCode;
	protected $returnObject;

	protected $client;
	protected $bAuthResult;

	public function __construct($id_user, $token){
		$this->returnObject = new \stdClass();
		$returnObject = $this->returnObject;

		$this->id_user = $id_user;
		$this->token = $token;	

		$this->tokenHealthChecker = new \EvernoteTokenHealthChecker($this->token);
		$this->errorCode = $this->tokenHealthChecker->getTokenHealth();
		$this->rateLimitDuration = 0;

		if ($this->errorCode === EvernoteTokenHealth::HEALTHY) {
			$noteStore = $this->tokenHealthChecker->loadNoteStore();
			if ($noteStore == null) {
				$returnObject->status = 498;
				$returnObject->message = 'Evernote Token Error';
				$returnObject->platform = 'Evernote';
				$returnObject->errorCode = $this->tokenHealthChecker->getErrorCode();
				$returnObject->rateLimitDuration = $this->tokenHealthChecker->getRateLimit();
				echo json_encode($returnObject);
				exit;
				return;
			}
			else {
				$this->noteStore = $noteStore;
			}
		} else {
			$returnObject->status = 498;
			$returnObject->message = 'Evernote Token Error';
			$returnObject->platform = 'Evernote';
			$returnObject->errorCode = $this->tokenHealthChecker->getErrorCode();
			$returnObject->rateLimitDuration = $this->tokenHealthChecker->getRateLimit();
			echo json_encode($returnObject);
			exit;
			return;
		}

		$userStore = $this->tokenHealthChecker->loadUserStore();
		if ($userStore == null) {
			$this->returnObject->errorCode = $this->tokenHealthChecker->getErrorCode();
			$this->returnObject->message = 'Error loading user store';
			echo json_encode($returnObject);
			exit;
			return;
		}
		$this->userStore = $userStore;

		$this->client = new \Evernote\AdvancedClient($this->token, false);

		$this->bAuthResult = (isset($_SESSION['bAuthResult']))?$_SESSION['bAuthResult']: null; // check last business auth token
		$this->bNoteStore = (isset($_SESSION['bNoteStore']))?$_SESSION['bNoteStore']: null; // 
		session_write_close(); // all necessary information from the session has been gathered

	}

	function authenticateToBusiness(){
		$ourUser = $this->userStore->getUser($this->token);
		if(!isset($ourUser->accounting->businessId)){
			$returnObject->status = 400;
			$returnObject->message = 'Not a buisness user';
			return $returnObject;
		}

		// Authenticate with Evernote Business, return AuthenticationResult instance
		try{
			$this->bAuthResult = $this->userStore->authenticateToBusiness($this->token);
		} catch (EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		session_start();
		$_SESSION['bAuthResult'] = $this->bAuthResult;

		$bNoteStore = $this->client->getBusinessNoteStore();
		$_SESSION['bNoteStore'] = $bNoteStore;

		session_write_close();


		return $this->bAuthResult;
	}



	function createBusinessNotebook($title, $buisnessID = null, $attemptCounter = 0){
		$returnObject = $this->returnObject;
		
		if($this->bAuthResult == null || $this->bNoteStore == null){//make sure tokens have been set, maybe expired if so will be caise below
			$this->authenticateToBusiness();
		}

		$bAuthResult = $this->bAuthResult;
		if (isset($bAuthResult->status) && $bAuthResult->status == 400){ // error, return error
			return $bAuthResult;
		}	

		$client = $this->client;

		$authToken = $this->token;
		$noteStore = $this->noteStore;
		
		$bAuthToken = $this->bAuthResult->authenticationToken;
		$bNoteStore = $this->bNoteStore;


		// Create notebook object
		$newNotebook = new Notebook();
		$newNotebook->name = $title;
		
		try{
			$newNotebook = $bNoteStore->createNotebook($bAuthToken, $newNotebook);
		} catch (EDAMUserException $e){
			if($e->errorCode == 9){//auth expired, attenot to reset and try again
				$this->authenticateToBusiness();
				if($attemptCounter < 1){
					$returnObject->attemptedToReAuthBus = true;
					return $this->newTagBusinessNote($title, $buisnessID, 1);
				}
			}
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}
		
		$sharedNotebook = $newNotebook->sharedNotebooks[0];

		$newLinkedNotebook = new LinkedNotebook();

		$newLinkedNotebook->shareName = $title;
		$newLinkedNotebook->shareKey = $sharedNotebook->shareKey;
		$newLinkedNotebook->username = $bAuthResult->user->username;
		$newLinkedNotebook->shardId = $bAuthResult->user->shardId;
		

		try {
			$newLinkedNotebook = $noteStore->createLinkedNotebook($authToken, $newLinkedNotebook);
		} catch (EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}
		
		$returnObject->status = 100;
		
		$note = array();
		$note['GUID'] = $newLinkedNotebook->{'guid'};
		$note['title'] = $newLinkedNotebook->{'shareName'};
		
		$returnObject->payload = $note;
		return $returnObject;
	}



	function createLinkedNote($title, $LINKED_notebookGUID, $SHARED_notebookGUID, $attemptCounter = 0){
		$returnObject = $this->returnObject;
		
		$authToken = $this->token;



		if($this->bAuthResult == null || $this->bNoteStore == null){//make sure tokens have been set, maybe expired if so will be caise below
			$this->authenticateToBusiness();
		}

		$bAuthResult = $this->bAuthResult;
		if (isset($bAuthResult->status) && $bAuthResult->status == 400){ // error, return error
			return $bAuthResult;
		}	

		$bAuthToken = $this->bAuthResult->authenticationToken;
		$bNoteStore = $this->bNoteStore;



		$client = $this->client;

		$noteStore = $this->noteStore;

		if($LINKED_notebookGUID != 'false'){ //I think you can change the shared note store to the bNoteStore?? maybe maybe not to test at some stage 
			# get linked notebooks
			$linked_notebooks = $noteStore->listLinkedNotebooks($authToken);
			
			$linked_notebook = null;
			foreach ($linked_notebooks as $notebook) {
				// if (isset($notebook->businessId) && $notebook->guid == $parentNotebookGUID){
				if ($notebook->guid == $LINKED_notebookGUID){
					$linked_notebook = $notebook;
					continue;
				}
			}
			if($linked_notebook === null){
				$returnObject->status = 400;
				$returnObject->message = 'No match for LINKED_notebookGUID found';
				return $returnObject;
			}

			# shareKey for the notebook
			$shareKey = $linked_notebook->shareKey;

			# get the right noteStore
			$note_store_uri = $linked_notebook->noteStoreUrl;
			try{
				$shared_note_store = $client->getSharedNoteStore($linked_notebook);
			} catch (EDAMUserException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			} catch (EDAMSystemException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			} catch(EDAMNotFoundException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			}
			# authenticate to the linked notebook
			$auth_result = $shared_note_store->authenticateToSharedNotebook($shareKey, $authToken);

			# get the share token
			$share_token = $auth_result->authenticationToken;

			# get the shared notebook
			try{
				$sharedNotebook = $shared_note_store->getSharedNotebookByAuth($share_token);
			} catch (EDAMUserException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			} catch (EDAMSystemException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			}

			$SHARED_notebookGUID = $sharedNotebook->notebookGuid;
		}

		$noteBody = "";
		$nBody = '<?xml version="1.0" encoding="UTF-8"?>';
		$nBody .= '<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">';
		$nBody .= '<en-note>' . $noteBody . '</en-note>';

		// Create note object
		$newNote = new Note();
		$newNote->title = $title;
		$newNote->notebookGuid = $SHARED_notebookGUID;
		$newNote->content = $nBody;

		try{
			$newNote = $bNoteStore->createNote($bAuthToken, $newNote);
		} catch (EDAMUserException $e){
			if($e->errorCode == 9){//auth expired, attenot to reset and try again
				$this->authenticateToBusiness();
				if($attemptCounter < 1){
					$returnObject->attemptedToReAuthBus = true;
					return $this->createLinkedNote($title, $LINKED_notebookGUID, $SHARED_notebookGUID, 1);
				}
			}
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}
		
		$returnObject->status = 100;
		
		$note = array();
		$note['GUID'] = $newNote->{'guid'};
		$note['title'] = $newNote->{'title'};
		$note['notebookGuid'] = $newNote->{'notebookGuid'};
		
		$returnObject->payload = $note;
		return $returnObject;
	}



	function newTagBusinessNote($title, $noteGUID, $attemptCounter = 0){
	
		$returnObject = $this->returnObject;
		$returnObject->status = 100;

		if($this->bAuthResult == null || $this->bNoteStore == null){//make sure tokens have been set, maybe expired if so will be caise below
			$this->authenticateToBusiness();
		}

		$bAuthResult = $this->bAuthResult;
		if (isset($bAuthResult->status) && $bAuthResult->status == 400){ // error, return error
			return $bAuthResult;
		}	

		$bAuthToken = $this->bAuthResult->authenticationToken;
		$bNoteStore = $this->bNoteStore;
		

		//NOTE FROM AMMAR - You don't actually need to fetch a note to set tags on it, all you need to do is construct a Note object, set the "guid", "title"
		// 					and "tagGuids" and/or "tagNames" fields, and call updateNote with that. Since you're not setting the content or other fields, they won't get updated. 
		// 					If you still want to fetch the note via getNote, make sure you're fetching it using the minimum data you need (eg without resources or content),
		// 					since that extra information can slow down your fetch.

		//get note
		try{
			$note = $bNoteStore->getNote($bAuthToken, $noteGUID, false, false, false, false);
		} catch(EDAMUserException $e){
			if($e->errorCode == 9){//auth expired, attenot to reset and try again
				$this->authenticateToBusiness();
				if($attemptCounter < 1){
					$returnObject->attemptedToReAuthBus = true;
					return $this->newTagBusinessNote($title, $noteGUID, 1);
				}
			}
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		$currentTagGuids = array();
		if(isset($note->tagGuids)){
			foreach ($note->tagGuids as $guid) {
				$currentTagGuids[] = $guid;
			}
		}

		$note->tagNames[] = $title;

		try{
			$updatedNote = $bNoteStore->updateNote($bAuthToken, $note);
		}  catch(EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		$newTagGuid = "tag already exsist";	
		foreach ($updatedNote->tagGuids as $newGuid) {
			if(!in_array($newGuid, $currentTagGuids)){
				$newTagGuid = $newGuid;
				continue;
			}
		}


		$returnObject->GUID = $newTagGuid;
		return $returnObject;
	}


	function addBusinessNotebookToStack($title, $notebookGUID){
		$returnObject = $this->returnObject;
		
		$authToken = $this->token;

		$client = $this->client;

		$noteStore = $this->noteStore;

		# get linked notebooks
		try{
			$linked_notebooks = $noteStore->listLinkedNotebooks($authToken);
		} catch (EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}  catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} 

		$linked_notebook = null;
		foreach ($linked_notebooks as $notebook) {
			if($notebook->guid == $notebookGUID){
				$linked_notebook = $notebook;
				continue;
			}
		}
		if($linked_notebook === null){
			$returnObject->status = 400;
			$returnObject->message = 'No match for notebookGUID found';
			return $returnObject;
		}

		$linked_notebook->stack = $title;

		try{
			$updatedNotebook = $noteStore->updateLinkedNotebook($authToken, $linked_notebook);
		} catch (EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} 
		$returnObject->status = 100;

		return $returnObject;
	}



	function renameBusinessNotebook($title, $notebookGUID){
		$returnObject = $this->returnObject;
		$returnObject->status = 100;

		$authToken = $this->token;

		$client = $this->client;
		
		$noteStore = $this->noteStore;

		# get linked notebooks
		try{
			$linked_notebooks = $noteStore->listLinkedNotebooks($authToken);
		} catch (EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}  catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} 
		
		$linked_notebook = null;
		foreach ($linked_notebooks as $notebook) {
			if($notebook->guid == $notebookGUID){
				$linked_notebook = $notebook;
				continue;
			}
		}

		if($linked_notebook === null){
			$returnObject->status = 400;
			$returnObject->message = 'No match for notebookGUID found';
			return $returnObject;
		}

		$linked_notebook->shareName = $title;
		// echo var_dump($linked_notebook);
		// exit;
		try{
			$updatedNotebook = $noteStore->updateLinkedNotebook($authToken, $linked_notebook);
		} catch (EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch (EDAMSystemException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} 

		$returnObject->status = 100;

		return $returnObject;
	}



	function renameBusinessNote($title, $noteGUID, $attemptCounter = 0){
		$returnObject = $this->returnObject;
		$returnObject->status = 100;

		if($this->bAuthResult == null || $this->bNoteStore == null){//make sure tokens have been set, maybe expired if so will be caise below
			$this->authenticateToBusiness();
		}

		$bAuthResult = $this->bAuthResult;
		if (isset($bAuthResult->status) && $bAuthResult->status == 400){ // error, return error
			return $bAuthResult;
		}	

		$bAuthToken = $this->bAuthResult->authenticationToken;
		$bNoteStore = $this->bNoteStore;

		try{
			$note = $bNoteStore->getNote($bAuthToken, $noteGUID, false, false, false, false);
		} catch(EDAMUserException $e){
			if($e->errorCode == 9){//auth expired, attenot to reset and try again
				$this->authenticateToBusiness();
				if($attemptCounter < 1){
					$returnObject->attemptedToReAuthBus = true;
					return $this->renameBusinessNote($title, $noteGUID, 1);
				}
			}
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		$note->title = $title; //Set new title

		try{
			$updatedNote = $bNoteStore->updateNote($bAuthToken, $note);
		}  catch(EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		return $returnObject;
	}



	function setReminderBusinessNote($timestamp, $noteGUID, $setDoneTime, $attemptCounter = 0){
		$returnObject = $this->returnObject;
		$returnObject->status = 100;

		if($this->bAuthResult == null || $this->bNoteStore == null){//make sure tokens have been set, maybe expired if so will be caise below
			$this->authenticateToBusiness();
		}

		$bAuthResult = $this->bAuthResult;
		if (isset($bAuthResult->status) && $bAuthResult->status == 400){ // error, return error
			return $bAuthResult;
		}	

		$bAuthToken = $this->bAuthResult->authenticationToken;
		$bNoteStore = $this->bNoteStore;

		try{
			$note = $bNoteStore->getNote($bAuthToken, $noteGUID, false, false, false, false);
		} catch(EDAMUserException $e){
			if($e->errorCode == 9){//auth expired, attenot to reset and try again
				$this->authenticateToBusiness();
				if($attemptCounter < 1){
					$returnObject->attemptedToReAuthBus = true;
					return $this->setReminderBusinessNote($timestamp, $noteGUID, $setDoneTime, 1);
				}
			}
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		$timestamp = (float)$timestamp;
		if($timestamp <= 0){//remove attributes
			if(!$setDoneTime){ //set done time is true when only modifying done time
				if(isset($note->attributes->reminderTime)){
					$note->attributes->reminderTime = null; //setting null doesnt seem to removeremove it from evernote..
				}
				if(isset($note->attributes->reminderDoneTime)){//if removing reminder removed compelted
					$note->attributes->reminderDoneTime = null;
				}
			} else {//only removed compelted
				if(isset($note->attributes->reminderDoneTime)){//if removing reminder removed compelted
					$note->attributes->reminderDoneTime = null;
				}
			}
		} else {
			if($setDoneTime){
				$note->attributes->reminderDoneTime = (float)$timestamp;
			} else {
				$note->attributes->reminderTime = (float)$timestamp;
				if(time()*1000 < $timestamp){//When a user sets a reminder time on a note that has a reminder done time, and that reminder time is in the future, then the reminder done time should be cleared
					if(isset($note->attributes->reminderDoneTime)){
						$note->attributes->reminderDoneTime = null;
					}
				}

			}
		}

		try{
			$updatedNote = $bNoteStore->updateNote($bAuthToken, $note);
		}  catch(EDAMUserException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		return $returnObject;
	}



	function copyBusinessNote($noteGUID, $LINKED_notebookGUID, $SHARED_notebookGUID, $attemptCounter = 0){
		$returnObject = $this->returnObject;
		$returnObject->status = 100;

		if($this->bAuthResult == null || $this->bNoteStore == null){//make sure tokens have been set, maybe expired if so will be caise below
			$this->authenticateToBusiness();
		}

		$bAuthResult = $this->bAuthResult;
		if (isset($bAuthResult->status) && $bAuthResult->status == 400){ // error, return error
			return $bAuthResult;
		}	

		$bAuthToken = $this->bAuthResult->authenticationToken;
		$bNoteStore = $this->bNoteStore;


		$authToken = $this->token;


		$client = $this->client;

		$noteStore = $this->noteStore;

		if($LINKED_notebookGUID != 'false'){ //use linked GUID to get shared GUID

			# get linked notebooks
			$linked_notebooks = $noteStore->listLinkedNotebooks($authToken);
			
			$linked_notebook = null;
			foreach ($linked_notebooks as $notebook) {
				if ($notebook->guid == $LINKED_notebookGUID){
					$linked_notebook = $notebook;
					continue;
				}
			}
			if($linked_notebook === null){
				$returnObject->status = 400;
				$returnObject->message = 'No match for parentNotebookGUID found';
				return $returnObject;
			}
			# shareKey for the notebook
			$shareKey = $linked_notebook->shareKey;

			# get the right noteStore
			$note_store_uri = $linked_notebook->noteStoreUrl;
			try{
				$shared_note_store = $client->getSharedNoteStore($linked_notebook);
			} catch (EDAMUserException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			} catch (EDAMSystemException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			} catch(EDAMNotFoundException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			}
			# authenticate to the linked notebook
			$auth_result = $shared_note_store->authenticateToSharedNotebook($shareKey, $authToken);

			# get the share token
			$share_token = $auth_result->authenticationToken;

			# get the shared notebook
			try{
				$sharedNotebook = $shared_note_store->getSharedNotebookByAuth($share_token);
			} catch (EDAMUserException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			} catch (EDAMSystemException $e){
				$returnObject->status = 400;
				$returnObject->message = json_encode($e);
				return $returnObject;
			}

			$SHARED_notebookGUID = $sharedNotebook->notebookGuid;

		}

		try{
			$note = $bNoteStore->copyNote($bAuthToken, $noteGUID, $SHARED_notebookGUID);
		} catch(EDAMUserException $e){
			if($e->errorCode == 9){//auth expired, attenot to reset and try again
				$this->authenticateToBusiness();
				if($attemptCounter < 1){
					$returnObject->attemptedToReAuthBus = true;
					return $this->copyBusinessNote($noteGUID, $LINKED_notebookGUID, $SHARED_notebookGUID, 1);
				}
			}
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		} catch(EDAMNotFoundException $e){
			$returnObject->status = 400;
			$returnObject->message = json_encode($e);
			return $returnObject;
		}

		$returnObject->GUID = $note->guid;
		return $returnObject;
	}
}

//get interface
if (isset($_GET['callType'])) {
	if($_GET['callType'] === 'createLinkedNote'){
		$title = $_GET['title'];
		$LINKED_notebookGUID = (isset($_GET['LINKED_notebookGUID']))?$_GET['LINKED_notebookGUID']:'false'; //will bet set as a string false if not set
		$SHARED_notebookGUID = (isset($_GET['SHARED_notebookGUID']))?$_GET['SHARED_notebookGUID']:'false'; //will bet set as a string false if not set

		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->createLinkedNote($title, $LINKED_notebookGUID, $SHARED_notebookGUID);
		echo json_encode($results);
	}
	if($_GET['callType'] === 'createBusinessNotebook'){
		$title = $_GET['title'];
		$buisnessID = (isset($_GET['buisnessID']))?$_GET['buisnessID']:null;
		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->createBusinessNotebook($title, $buisnessID);
		echo json_encode($results);
	}
	if($_GET['callType'] === 'tagBusinessNote'){
		$title = $_GET['title'];
		$noteGUID = $_GET['noteGUID'];
		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->newTagBusinessNote($title, $noteGUID);
		echo json_encode($results);
	}
	if($_GET['callType'] === 'addBusinessNotebookToStack'){
		$title = $_GET['title'];
		$notebookGUID = $_GET['notebookGUID'];
		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->addBusinessNotebookToStack($title, $notebookGUID);
		echo json_encode($results);
	}
	if($_GET['callType'] === 'renameBusinessNotebook'){
		$title = $_GET['title'];
		$GUID = $_GET['GUID'];
		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->renameBusinessNotebook($title, $GUID);
		echo json_encode($results);
	}	
	if($_GET['callType'] === 'renameBusinessNote'){
		$title = $_GET['title'];
		$GUID = $_GET['GUID'];
		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->renameBusinessNote($title, $GUID);
		echo json_encode($results);
	}	
	if($_GET['callType'] === 'setReminderBusinessNote'){
		$timestamp = $_GET['timestamp'];
		$GUID = $_GET['GUID'];
		$setDoneTime = (isset($_GET['setDoneTime']))?$_GET['setDoneTime']:false;
		$setDoneTime = filter_var($setDoneTime, FILTER_VALIDATE_BOOLEAN); 
		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->setReminderBusinessNote($timestamp, $GUID, $setDoneTime);
		echo json_encode($results);
	}	
	if($_GET['callType'] === 'copyBusinessNote'){
		$noteGUID = $_GET['noteGUID'];
		$LINKED_notebookGUID = (isset($_GET['LINKED_notebookGUID']))?$_GET['LINKED_notebookGUID']:'false'; //will bet set as a string false if not set
		$SHARED_notebookGUID = (isset($_GET['SHARED_notebookGUID']))?$_GET['SHARED_notebookGUID']:'false'; //will bet set as a string false if not set

		$EvernoteAPIInterface = new EvernoteAPIInterface($fgmembersite);
		$results = $EvernoteAPIInterface->copyBusinessNote($noteGUID,$LINKED_notebookGUID, $SHARED_notebookGUID);
		echo json_encode($results);
	}
}


?>
