<?php
// Author Jack Z K Davies. Jack.z.k.davies@gmail.com.
//
// *** READE ME ***
// This code is an *outline only* due to NDA surrounding the full orignal code, 
// and is intended to give an overview of the process behind
// syncing an account from Evernote to a Neo4j databse only. 
// Due to this some classes used are not defined here and this code is untested in this exact format
//

use EDAM\NoteStore\NoteStoreClient;
use EDAM\NoteStore\NoteFilter;
use EDAM\NoteStore\NotesMetadataResultSpec;

use EDAM\Error\EDAMSystemException,
	EDAM\Error\EDAMUserException,
	EDAM\Error\EDAMErrorCode,
	EDAM\Error\EDAMNotFoundException;

use Thrift\Exception\TTransportException;

date_default_timezone_set("UTC"); 

class EvernoteProcessor {

	protected $useQueueDriver;
	protected $id_user;
	protected $name;
	protected $username;
	protected $email;
	protected $platform_id;
	protected $token;
	protected $account_id;
	protected $evernoteClient;
	protected $noteStore;
	protected $syncState;
	protected $neo4jClient;
	protected $startIndex;
	protected $startTime;
	protected $returnObject;
	protected $verbose;
	protected $offset;
	protected $latestUpdateCount;
	protected $lastSyncTimestamp;
	protected $errorCode;

	protected $LINKED_offset;
	protected $LINKED_latestUpdateCount;
	protected $LINKED_lastSyncTimestamp;

	public function __construct($id_user = false, $token = false, $encryption_key = false){
		$this->verbose = false;
		if (isset($_REQUEST['verbose'])) {
			$this->verbose = true;
			echo 'Verbose mode on<br/>';
		}
		
		$this->id_user = $id_user;
		$this->token = $token;
		$this->account_id = ""; //This value is to be set from API during initiateProcessing()
		$encryption_key = $encryption_key;
		$this->encryptionHelper = new \EncryptionHelper($encryption_key);

	}
	
	function debugOutput($message) {
		if ($this->verbose) {
			echo $message;
		}
	}

	function initiateProcessing(){
		set_time_limit(120);

		$this->tokenHealthChecker = new \EvernoteTokenHealthChecker($this->token);
		$this->errorCode = $this->tokenHealthChecker->getTokenHealth();
		$this->rateLimitDuration = 0;

		if ($this->errorCode === EvernoteTokenHealth::HEALTHY) {
			$noteStore = $this->tokenHealthChecker->loadNoteStore();
			if ($noteStore == null) {
				$returnError = new \stdClass();
				$returnError->status = 498;
				$returnError->message = 'Evernote Token Error';
				$returnError->platform = 'Evernote';
				$returnError->errorCode = $this->tokenHealthChecker->getErrorCode();
				$returnError->rateLimitDuration = $this->tokenHealthChecker->getRateLimit();
				return $returnError;
				
			}
		} else {
			$returnError = new \stdClass();
			$returnError->status = 498;
			$returnError->message = 'Evernote Token Error';
			$returnError->platform = 'Evernote';
			$returnError->errorCode = $this->tokenHealthChecker->getErrorCode();
			$returnError->rateLimitDuration = $this->tokenHealthChecker->getRateLimit();
			return $returnError;
		}
		
		$this->neo4jClient = \Neo4jHelper::getClient($this->id_user);
		// uncomment for sync testing from begining
		// \MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'offset', 0);
		// \MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'latestUpdateCount', 0);
		// \MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'lastSyncTimestamp', 0);

		//Set up return object 
		$this->startTime = time();
		$this->returnObject = new \stdClass(); 
		$returnObject = $this->returnObject;

		// Get user account id information
		$userStore = $this->tokenHealthChecker->loadUserStore();

		try{
			$user = $userStore->getUser();
		}
		catch(EDAMUserException $e){
			$this->debugOutput($e, 1);
			error_log($e, 0);
			$returnObject->status = $e->errorCode;
			$returnObject->message = $e->parameter;
			$returnObject->user = $user;
			$returnObject->error = $e;
			return $returnObject;
		}

	
		if(isset($user->id)){
			$this->account_id = $this->encryptionHelper->encrypt((string)$user->id);
		} else {
			$returnObject->status = 503;
			$returnObject->message = 'No user id set';
			$returnObject->user = $user;
			return $returnObject;
		}

		if(!isset($user->username)){$user->username = '';}
		if(!isset($user->email)){$user->email = '';}
		if(!isset($user->name)){$user->name = '';}
		$this->username = $this->encryptionHelper->encrypt((string)$user->username);
		$this->email = $this->encryptionHelper->encrypt((string)$user->email);
		$this->name = $this->encryptionHelper->encrypt((string)$user->name);
		
		//Add account information to root cloud node in Neo4j
		\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'account_id', $this->account_id);
		\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'account_name', $this->encryptionHelper->encrypt($user->name));
		\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'account_email', $this->encryptionHelper->encrypt($user->email));
		\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'account_username', $this->encryptionHelper->encrypt($user->username));

		$latestUpdateCount  = \MetadataUtils::readAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id , "latestUpdateCount");
		if($latestUpdateCount === null){
			$this->latestUpdateCount = 0;
		}
		else{
			$this->latestUpdateCount = $latestUpdateCount;
		}

		$offset = \MetadataUtils::readAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, "offset");
		if($offset === null){
			$this->offset = 0;
		}
		else{
			$this->offset = $offset;
		}

		$lastSyncTimestamp  = \MetadataUtils::readAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id , "lastSyncTimestamp");
		if($lastSyncTimestamp === null){
			$this->lastSyncTimestamp = 0;
		}
		else{
			$this->lastSyncTimestamp = $lastSyncTimestamp;
		}	


		$returnObject->status = 250; // interrupted status, this will be updated during beginProcessing() and getLinkedNotebooks() on success/fail
		$returnObject->personal = new \stdClass();
		$returnObject->linked = new \stdClass();

		$this->beginProcessing();
		$this->getLinkedNotebooks();

		return $this->getReturnOject();
	}

	public function getReturnOject(){
		//Chech each return for completeness. For testing assuming all complete.

		$returnObject = $this->returnObject;
		
		$personal = $returnObject->personal;
		$personalHasMore = $personal->hasMore;
		$personalFailed = false;
		if($personal->status != 100){$personalFailed = true;}
		$personalFoldersProcessed = $personal->foldersProcessed;

		$linked = $returnObject->linked;
		$linkedFailed = false;
		$linkedHasMore = false;
		$linkedFoldersProcessed = false;
		foreach ($linked as $key => $value) {
			if ($value->status != 100){$linkedFailed = true;}
			if ($value->hasMore){$linkedHasMore = true;}
			$linkedFoldersProcessed += $value->foldersProcessed;
		}
		
		$status = 200;
		if(!$personalFailed && !$linkedFailed){
			$status = 100;
		}
		$hasMore = $linkedHasMore || $personalHasMore;
		$foldersProcessed = $personalFoldersProcessed + $linkedFoldersProcessed;

		$currentTime = time();
		$runTime = ($currentTime - $this->startTime);
		$returnObject = new \stdClass();
		$returnObject->status = $status;
		$returnObject->hasMore = $hasMore;
		$returnObject->processBeginTime = $this->startTime;
		$returnObject->runTime = $runTime;
		$returnObject->foldersProcessed = $foldersProcessed;
		return $returnObject;
	}

	function returnError($error){
		$currentTime = time();
		$runTime = ($currentTime - $this->startTime);
		$returnError = new \stdClass();
		$returnError->status = 503 ;
		$returnError->runTime = $runTime;
		$returnError->error = $error;
		$returnError->hasMore = false;
		$returnError->foldersProcessed = 0;
		$returnError->processed_count = 0; // added this to match other platforms return type
		$returnError->errorCode = $this->errorCode;
		$returnError->rateLimitDuration = $this->tokenHealthChecker->getRateLimit();
		return $returnError;
	}

	public function getSyncState(){
		if (is_null($this->syncState)) {
			$noteStore = $this->tokenHealthChecker->loadNoteStore();
			if ($noteStore == null) {
				$this->errorCode = $this->tokenHealthChecker->getErrorCode();
				$this->returnError('Error loading note store');
			}

			try{
				$this->syncState = $noteStore->getSyncState();
			}catch(TTransportException $e){
				$this->debugOutput($e, 1);
				error_log($e, 0);
				$this->returnError($e);
			}
		}
		return $this->syncState;
	}

	public function beginProcessing(){
		$returnObject = $this->returnObject->personal;
		$syncState = $this->getSyncState();
		$currentUpdateCount = $syncState->updateCount;

		$returnObject->latestUpdateCount = $this->latestUpdateCount;
		$returnObject->currentUpdateCount = $currentUpdateCount;

		if($currentUpdateCount > $this->latestUpdateCount){
			//check for differences
			$currentTime = time();
			$runTime = ($currentTime - $this->startTime);
			if($runTime > 30){
				//Only process as many loops as possible in 30 seconds to avoid php time outs.
				$this->debugOutput('Run time reached. Runtime: '.$runTime,1);
				continue;
			}

			$noteStore = $this->tokenHealthChecker->loadNoteStore();
			if ($noteStore == null) {
				$this->errorCode = $this->tokenHealthChecker->getErrorCode();
				$this->returnError('Error loading note store');
			}

			$filter = new NoteFilter();
			$spec = new NotesMetadataResultSpec();
					$spec->includeTitle = true;
					$spec->includeContentLenght = true;
					$spec->includeCreated = true;
					$spec->includeUpdated = true;
					$spec->includeUpdateSequenceNum = true;
					$spec->includeNotebookGuid = true;
					$spec->includeTagGuids = true;
					$spec->includeAttributes = true;
					$spec->includeLargestResourceMime = true;
					$spec->includeLargestResourceSize = true;

			$offset = $this->offset;
			$max = 1000;

			try{
				$notes = $noteStore->findNotesMetadata($this->token, $filter, $offset, $max, $spec);
			}
			catch(EDAMNotFoundException $e){
				// sometimes notebooks get unshared or you lose access, and it will throw exception
				// skip these notebooks
				$this->debugOutput($e, 1);
				error_log($e, 0);
				continue;
			}

			if(isset($notes->notes)){
				$batchEntries = [];
				foreach ($notes->notes as $note){
					if ($note->updated > $this->lastSyncTimestamp || $note->created > $this->lastSyncTimestamp){
						$batchEntries[] = $note;
					}
				}
				$this->batchWriteItemsWithAnyParent($batchEntries);

				$newOffset = $offset + count($notes->notes);
				if($newOffset >= $notes->totalNotes){
					\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'offset', 0);
					\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'latestUpdateCount', $currentUpdateCount);
					\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'lastSyncTimestamp', $syncState->currentTime);

					$newOffset = 'finished';
					$hasMore = false;
				}
				else{
					\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, 'offset', $newOffset);
					$hasMore = true;
				}
			}	

			$tags = $noteStore->listTags($this->token);
			$this->batchWriteTagsMeta($tags);

			try{
				$notebooks = $noteStore->listNotebooks($this->token);
			}
			catch(EDAMNotFoundException $e){
				$this->debugOutput($e, 1);
				error_log($e, 0);
				continue;
			}
			$this->batchWriteNotebooks($notebooks);

			$returnObject->status = 100;
			$returnObject->hasMore = $hasMore;
			$returnObject->offset = $newOffset;
			$returnObject->foldersProcessed = count($batchEntries);

		}
		else{
			$returnObject->status = 100;
			$returnObject->hasMore = false;
			$returnObject->offset = "Already up to date";
			$returnObject->foldersProcessed = 0;
		}

	}

	public function getLinkedNotebooks(){
		$returnObject = $this->returnObject->linked;

		$evernoteClient = $this->tokenHealthChecker->loadEvernoteClient();
		$noteStore = $this->tokenHealthChecker->loadNoteStore();
		if ($noteStore == null) {
			$this->errorCode = $this->tokenHealthChecker->getErrorCode();
			$this->returnError('Error loading note store');
		}
		try{
			$linkedNotebooks = $noteStore->listLinkedNotebooks($this->token);
		}
		catch(EDAMNotFoundException $e){
			$this->debugOutput($e, 1);
			error_log($e, 0);
			continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
		}catch(EDAMUserException $e){
			$this->debugOutput($e, 1);
		 	error_log($e, 0);
		 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
		}catch(EDAMSystemException  $e){
			$this->debugOutput($e, 1);
		 	error_log($e, 0);
		 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
		}


		$noteStores = new \stdClass();
		$changesMade = false;

		foreach ($linkedNotebooks as $notebook) {
			if ($notebook->shareKey == null){
				continue; //is public
			}
			$currentTime = time();
			$runTime = ($currentTime - $this->startTime);
			if($runTime > 30){
				//Only process as many loops as possible in 30 seconds to avoid php time outs.
				$this->debugOutput('Run time reached. Runtime: '.$runTime,1);
				continue;
			}

			$shareKeyHash = substr(strtolower(preg_replace('/[0-9_\/]+/','',base64_encode(sha1($notebook->shareKey)))),0,8);

			$returnObject->$shareKeyHash = new \stdClass();

			$linkedNoteReturn = $returnObject->$shareKeyHash;
			$linkedNoteReturn->status = 100;
			$linkedNoteReturn->hasMore = false;
			$linkedNoteReturn->offset = "Already up to date";
			$linkedNoteReturn->foldersProcessed = 0;
		
			// Uncomment for testing;
			// \MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_offset", 0);
			// \MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_latestUpdateCount", 0);
			// \MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_lastSyncTimestamp", 0);

			$LINKED_latestUpdateCount  = \MetadataUtils::readAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id , $shareKeyHash."_latestUpdateCount");
			if($LINKED_latestUpdateCount === null){
				$this->LINKED_latestUpdateCount = 0;
			}
			else{
				$this->LINKED_latestUpdateCount = $LINKED_latestUpdateCount;
			}

			$LINKED_offset = \MetadataUtils::readAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_offset");
			if($LINKED_offset === null){
				$this->LINKED_offset = 0;
			}
			else{
				$this->LINKED_offset = $LINKED_offset;
			}

			$LINKED_lastSyncTimestamp  = \MetadataUtils::readAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id , $shareKeyHash."_lastSyncTimestamp");
			if($LINKED_lastSyncTimestamp === null){
				$this->LINKED_lastSyncTimestamp = 0;
			}
			else{
				$this->LINKED_lastSyncTimestamp = $LINKED_lastSyncTimestamp;
			}	

			$noteStoreUrl = $notebook->noteStoreUrl;
			
			$shardId = $notebook->shardId;
			if(!isset($noteStores->$shardId)){
				try{
					$linkedNoteStore = $evernoteClient->getSharedNoteStore($notebook);
					$noteStores->$shardId = $linkedNoteStore;
				}
				catch(EDAMNotFoundException $e){
					$this->debugOutput($e, 1);
					continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
				}catch(EDAMUserException $e){
					$this->debugOutput($e, 1);
				 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
				}catch(EDAMSystemException  $e){
					$this->debugOutput($e, 1);
				 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
				}
			}
			else{
				$linkedNoteStore = $noteStores->$shardId;
			}
			
			$shareKey = $notebook->shareKey;

			try{
				$authResult = $linkedNoteStore->authenticateToSharedNotebook($shareKey, $this->token);
			}
			catch(EDAMNotFoundException $e){
				$this->debugOutput($e, 1);
			 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			}catch(EDAMUserException $e){
				$this->debugOutput($e, 1);
			 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			}catch(EDAMSystemException  $e){
				$this->debugOutput($e, 1);
			 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			}
			$linkedToken = $authResult->authenticationToken;

			try{
				$syncState = $linkedNoteStore->getLinkedNotebookSyncState($linkedToken, $notebook);
			} catch(EDAMNotFoundException $e) {
				$this->debugOutput($e, 1);
				continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			} catch(EDAMSystemException $e) {
				$this->debugOutput($e, 1);
				continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			}catch(EDAMUserException $e){
				$this->debugOutput($e, 1);
			 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			}catch(EDAMSystemException  $e){
				$this->debugOutput($e, 1);
			 	continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
			}

			

			$currentUpdateCount = $syncState->updateCount;

			$linkedNoteReturn->currentUpdateCount = $currentUpdateCount;
			$linkedNoteReturn->latestUpdateCount = $LINKED_latestUpdateCount;

			if($currentUpdateCount > $this->LINKED_latestUpdateCount){
				$this->debugOutput( "Changes Found in linkedNotebook:".json_encode($notebook).'<br>',1);
				$changesMade = true;

				try{
					$linkedNotebook = $linkedNoteStore->getSharedNotebookByAuth($linkedToken);
				}catch(EDAMNotFoundException $e){
					$this->debugOutput($e, 1);
					continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
				}

				$filter = new NoteFilter();
				$spec = new NotesMetadataResultSpec();
						$spec->includeTitle = true;
						$spec->includeContentLenght = true;
						$spec->includeCreated = true;
						$spec->includeUpdated = true;
						$spec->includeUpdateSequenceNum = true;
						$spec->includeNotebookGuid = true;
						$spec->includeTagGuids = true;
						$spec->includeAttributes = true;
						$spec->includeLargestResourceMime = true;
						$spec->includeLargestResourceSize = true;

				$offset = $LINKED_offset;
				$max = 1000;

				try{
					$sharedNotes = $linkedNoteStore->findNotesMetadata($linkedToken, $filter, $offset, $max, $spec);
				}catch(EDAMNotFoundException $e){
					$this->debugOutput($e, 1);
					continue; // sometimes notebooks get unshared or you lose access, and it will throw exceptions, carry on to next item.
				}

				if(isset($sharedNotes->businessNotebook)){
					$is_business = true;
					$is_linked = false;
				} else {
					$is_business = false;
					$is_linked = true;
				}

				if(isset($sharedNotes->notes)){
					$batchEntries = [];
					foreach ($sharedNotes->notes as $note){
						if ($note->updated > $this->LINKED_lastSyncTimestamp || $note->created > $this->LINKED_lastSyncTimestamp){
							$note->notebookGuid = $notebook->guid;
							$note->shardId = $shardId;
							$note->shareKey = $shareKey;
							$note->is_business = $is_business;
							$note->is_linked = $is_linked;
							$batchEntries[] = $note;
						}
					}
					$this->batchWriteItemsWithAnyParent($batchEntries);

					$newOffset = $offset + count($sharedNotes->notes);
					if($newOffset >= $sharedNotes->totalNotes){
						\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_offset", 0);
						\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_latestUpdateCount", $currentUpdateCount);
						\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_lastSyncTimestamp", $syncState->currentTime);
						$newOffset = 'finished';
						$hasMore = false;
					} else{
						\MetadataUtils::writeAdminFieldStatic($this->neo4jClient, $this->platform_id, $this->id_user, $this->account_id, $shareKeyHash."_offset", $newOffset);
						$hasMore = true;
					}
				}

				$tags = $linkedNoteStore->listTagsByNotebook($linkedToken, $linkedNotebook->notebookGuid);
				$this->batchWriteTagsMeta($tags);

				$linkedNoteReturn->status = 100;
				$linkedNoteReturn->hasMore = $hasMore;
				$linkedNoteReturn->offset = $newOffset;
				$linkedNoteReturn->foldersProcessed = count($batchEntries);
			}
			else{
				$linkedNoteReturn->status = 100;
				$linkedNoteReturn->hasMore = false;
				$linkedNoteReturn->offset = "Already up to date";
				$linkedNoteReturn->foldersProcessed = 0;
			}
		}

		if($changesMade){
			$batchEntries = [];
			foreach ($linkedNotebooks as $notebook){
				$batchEntries[] = $notebook;
			}
			$this->batchWriteSharedNotebooks($batchEntries);
		}
	}



	function batchWriteItemsWithAnyParent($batchEntries){
		$this->debugOutput( '<pre> batchWriteItemsWithAnyParent:</pre>', 3);
		$this->debugOutput( '<pre>'.json_encode($batchEntries, JSON_PRETTY_PRINT).'</pre>', 1);

		$currentTime = time();
		$neo4jClient = $this->neo4jClient;
		$id_user = $this->id_user;
		$platform_id = $this->platform_id;
		$account_id = $this->account_id;
		$itemArrays = array();
		$tags = new \stdClass();

		foreach($batchEntries as $item) {

			if($item->modified == ''){
				$item->modified = false; //not always set
			}

			//Encrypt personal information
			if(isset ($item->attributes->lastEditedBy) && isset ($item->attributes->lastEditorId)){
				$item->modified_by_acc = $this->encryptionHelper->encrypt($item->attributes->lastEditorId);
				$item->modified_by_name = $item->attributes->lastEditedBy;
				$item->modified_by_email = '';
				$pos = strpos($item->modified_by_name,'<');
				$posEmail = strpos($item->modified_by_name,'@');

				if($posEmail !== false){
					$posEnd = strpos($item->modified_by_name,'>');
					$email = substr($item->modified_by_name, $pos+1, $posEnd);
					$email = str_replace(">","",$email);

					$item->modified_by_email = $this->encryptionHelper->encrypt($email);
				}
				if($pos !== false){
					$item->modified_by_name = $this->encryptionHelper->encrypt(substr($item->modified_by_name, 0, $pos-1));
				}
				else{
					$item->modified_by_name = $this->encryptionHelper->encrypt($item->modified_by_name);
				}

			}
			else{
				//When this values are not set, they are items owned by the user
				$item->modified_by_acc = $this->account_id;
				$item->modified_by_name = $this->name;
				$item->modified_by_email = $this->email;
			}
			if(!isset($item->shardId)){
				$item->shardId = '';
			}		
			if(!isset($item->shareKey)){
				$item->shareKey = '';
			}
			if(!isset($item->shareKey)){
				$item->shareKey = '';
			}
			if($item->deleted != null){
				$item->is_deleted = true;
			}
			else{
				$item->is_deleted = false;
			}
			if($item->tagGuids != null){
				$guid = strval($item->guid);
				$tagsArray = array();
				foreach ($item->tagGuids as $tag) {
					$tag = strval ($tag);
					$tagsArray[] = $tag;
				}
				$tags->$guid = $tagsArray;
			}

			$item->is_business = (isset($item->is_business))?$item->is_business:false;
			$item->is_linked = (isset($item->is_linked))?$item->is_linked:false;

			$itemArrays[] = $item;
		}

		$this->debugOutput('itemArrays', 1);
		$this->debugOutput( '<pre>'.json_encode($itemArrays, JSON_PRETTY_PRINT).'</pre>', 1);

		$params = array('items' => $itemArrays, 'userID' => "$id_user", 'accountID' => "$account_id", 'providerID' => "$platform_id" );

		$qry =
			"MERGE (c:Cloud { type:{providerID}, id_user:{userID}, account_id:{accountID} })".PHP_EOL.
			"WITH c".PHP_EOL.
			"UNWIND { items }".PHP_EOL.
			" AS items MERGE (i:Item { id:items.guid, account_id:{accountID}, id_user:{userID}, type:{providerID} })".PHP_EOL.
			
			"ON Create SET i = { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:false, is_business:items.is_business, name:items.title, description:items.description, shardId:items.shardId, shareKey:items.shareKey, size:items.contentLength, created_at:items.created, modified_at:items.modified, modified_by_name:items.modified_by_name, modified_by_acc:items.modified_by_acc, last_modifier_id:items.modified_by_acc, email:items.modified_by_email, processed:$currentTime, type:{providerID}, is_deleted:items.is_deleted}".PHP_EOL.
			"ON Match  SET i+= { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:false, is_business:items.is_business, name:items.title, description:items.description, shardId:items.shardId, shareKey:items.shareKey, size:items.contentLength, created_at:items.created, modified_at:items.modified, modified_by_name:items.modified_by_name, modified_by_acc:items.modified_by_acc, last_modifier_id:items.modified_by_acc, email:items.modified_by_email, processed:$currentTime, type:{providerID}, is_deleted:items.is_deleted}".PHP_EOL.

			"MERGE (p:Item { id:items.notebookGuid, id_user:{userID}, is_dir:true, account_id:{accountID}, type:{providerID}})".PHP_EOL.
			"MERGE (um:User { id_user:{userID}, id:items.modified_by_acc, name:items.modified_by_name, email:items.modified_by_email,  account_id:{accountID}, type:{providerID} })".PHP_EOL.
			"MERGE (i)-[:ModifiedBy]->(um)".PHP_EOL.
			"MERGE (p)-[:Contains]->(i)".PHP_EOL.
			"MERGE (c)-[:Owns]->(i)".PHP_EOL.
			"";

		$this->debugOutput('QUERY', 2);
		$this->debugOutput('<pre>'.$qry.'</pre>', 2);


		$this->handleQuery($neo4jClient, $qry, ":batchWriteItems", $params);
		$this->batchWriteTags($tags);
	}

	function batchWriteTagsMeta($tagsArray){
		$this->debugOutput( '<pre> batchWriteTagsMeta:</pre>', 3);
		$this->debugOutput( '<pre>'.json_encode($tagsArray, JSON_PRETTY_PRINT).'</pre>', 1);

		$currentTime = time();
		$neo4jClient = $this->neo4jClient;
		$id_user = $this->id_user;
		$platform_id = $this->platform_id;
		$account_id = $this->account_id;

		$this->debugOutput('tagsArray', 1);
		$this->debugOutput( '<pre>'.json_encode($tagsArray, JSON_PRETTY_PRINT).'</pre>', 1);

		$params = array('items' => $tagsArray, 'userID' => "$id_user", 'accountID' => "$account_id", 'providerID' => "$platform_id" );

		$qry =
			"MERGE (c:Cloud { type:{providerID}, id_user:{userID}, account_id:{accountID} })".PHP_EOL.
			"WITH c".PHP_EOL.
			"UNWIND { items }".PHP_EOL.
			" AS items MERGE (t:Tag { id:items.guid, account_id:{accountID}, id_user:{userID}, type:{providerID} })".PHP_EOL.
			
			"ON Create SET t = { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:false, name:items.name, updateSequenceNum:items.updateSequenceNum, size:items.contentLength, type:{providerID}, parentGuid:items.parentGuid}".PHP_EOL.
			"ON Match  SET t+= { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:false, name:items.name, updateSequenceNum:items.updateSequenceNum, size:items.contentLength, type:{providerID}, parentGuid:items.parentGuid}".PHP_EOL.
			"";

		$this->debugOutput('QUERY', 2);
		$this->debugOutput('<pre>'.$qry.'</pre>', 2);


		$this->handleQuery($neo4jClient, $qry, ":batchWriteTagsMeta", $params);
	}
	function batchWriteTags($batchEntries){
		$this->debugOutput( '<pre> batchWriteTags:</pre>', 3);
		$this->debugOutput( '<pre>'.json_encode($batchEntries, JSON_PRETTY_PRINT).'</pre>', 1);

		$currentTime = time();
		$neo4jClient = $this->neo4jClient;
		$id_user = $this->id_user;
		$platform_id = $this->platform_id;
		$account_id = $this->account_id;
		$itemArrays = array();

		foreach($batchEntries as $noteGuid => $tags) {
			foreach($tags as $tag){
				$item = new \stdClass();
				$item->tagGuid = $tag;
				$item->noteGuid = $noteGuid;
				$itemArrays[] = $item;
			}
		}

		$this->debugOutput('itemArrays', 1);
		$this->debugOutput( '<pre>'.json_encode($itemArrays, JSON_PRETTY_PRINT).'</pre>', 1);

		$params = array('items' => $itemArrays, 'userID' => "$id_user", 'accountID' => "$account_id", 'providerID' => "$platform_id" );

		$qry =
			"MERGE (c:Cloud { type:{providerID}, id_user:{userID}, account_id:{accountID} })".PHP_EOL.
			"WITH c".PHP_EOL.
			"UNWIND { items }".PHP_EOL.
			" AS items Match (i:Item { id:items.noteGuid, account_id:{accountID}, id_user:{userID}, type:{providerID} })".PHP_EOL.
		
			"MERGE (t:Tag { id:items.tagGuid, id_user:{userID}, is_dir:false, account_id:{accountID}, type:{providerID}})".PHP_EOL.

			"";

		$this->debugOutput('QUERY', 2);
		$this->debugOutput('<pre>'.$qry.'</pre>', 2);


		$this->handleQuery($neo4jClient, $qry, ":batchWriteTags", $params);	
	}

	function batchWriteNotebooks($batchEntries){
		$this->debugOutput( '<pre> batchWriteNotebooks:</pre>', 3);
		$this->debugOutput( '<pre>'.json_encode($batchEntries, JSON_PRETTY_PRINT).'</pre>', 1);

		$currentTime = time();
		$neo4jClient = $this->neo4jClient;
		$id_user = $this->id_user;
		$platform_id = $this->platform_id;
		$account_id = $this->account_id;
		$itemArrays = array();

		foreach($batchEntries as $item) {
			$item->is_deleted = false;
			$item->business_id = 0;
			$item->is_business = false;
			$item->is_linked = false;
			$props = \Neo4jHelper::makeItemArray($item, $id_user);
			$itemArrays[] = $props;
		}

		$this->debugOutput('itemArrays', 1);
		$this->debugOutput( '<pre>'.json_encode($itemArrays, JSON_PRETTY_PRINT).'</pre>', 1);

		$params = array('items' => $itemArrays, 'userID' => "$id_user", 'accountID' => "$account_id", 'providerID' => "$platform_id" );

		$qry =
			"MERGE (c:Cloud { type:{providerID}, id_user:{userID}, account_id:{accountID} })".PHP_EOL.
			"WITH c".PHP_EOL.
			"UNWIND { items }".PHP_EOL.
			" AS items MERGE (i:Item { id:items.guid, account_id:{accountID}, id_user:{userID}, type:{providerID} })".PHP_EOL.
			
			"ON Create SET i = { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:true, name:items.name, processed:$currentTime, type:{providerID}, is_deleted:items.is_deleted, business_id:items.business_id, is_business:items.is_business, is_linked:items.is_linked}".PHP_EOL.
			"ON Match  SET i+= { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:true, name:items.name, processed:$currentTime, type:{providerID}, is_deleted:items.is_deleted, business_id:items.business_id, is_business:items.is_business, is_linked:items.is_linked}".PHP_EOL.

			// "ON CREATE SET i = { id:items.modified_by_email, account_id:items.modified_by_acc, name:items.modified_by_name }".PHP_EOL.

			""; // end FOREACH

		$this->debugOutput('QUERY', 2);
		$this->debugOutput('<pre>'.$qry.'</pre>', 2);


		$this->handleQuery($neo4jClient, $qry, ":batchWriteItems", $params);
	}

	function batchWriteSharedNotebooks($batchEntries){
		$this->debugOutput( '<pre> batchWriteSharedNotebooks:</pre>', 3);
		$this->debugOutput( '<pre>'.json_encode($batchEntries, JSON_PRETTY_PRINT).'</pre>', 1);

		$currentTime = time();
		$neo4jClient = $this->neo4jClient;
		$id_user = $this->id_user;
		$platform_id = $this->platform_id;
		$account_id = $this->account_id;
		$itemArrays = array();

		foreach($batchEntries as $item) {
			$item->is_deleted = false;
			$business_id = (isset($item->businessId))?$item->businessId:0;
			$item->business_id = $business_id;
			$item->is_business = ($business_id != 0)?true:false;
			$item->is_linked = ($item->is_business)?false:true;
			$itemArrays[] = $item;
		}

		$this->debugOutput('itemArrays', 1);
		$this->debugOutput( '<pre>'.json_encode($itemArrays, JSON_PRETTY_PRINT).'</pre>', 1);

		$params = array('items' => $itemArrays, 'userID' => "$id_user", 'accountID' => "$account_id", 'providerID' => "$platform_id" );

		$qry =
			"MERGE (c:Cloud { type:{providerID}, id_user:{userID}, account_id:{accountID} })".PHP_EOL.
			"WITH c".PHP_EOL.
			"UNWIND { items }".PHP_EOL.
			" AS items MERGE (i:Item { id:items.guid, account_id:{accountID}, id_user:{userID}, type:{providerID} })".PHP_EOL.
			
			"ON Create SET i = { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:true, name:items.shareName, userName:items.username, processed:$currentTime, type:{providerID}, is_deleted:items.is_deleted, business_id:items.business_id, is_business:items.is_business, is_linked:items.is_linked}".PHP_EOL.
			"ON Match  SET i+= { id:items.guid, account_id:{accountID}, id_user:{userID}, is_dir:true, name:items.shareName, userName:items.username, processed:$currentTime, type:{providerID}, is_deleted:items.is_deleted, business_id:items.business_id, is_business:items.is_business, is_linked:items.is_linked}".PHP_EOL.

			"";

		$this->debugOutput('QUERY', 2);
		$this->debugOutput('<pre>'.$qry.'</pre>', 2);


		$this->handleQuery($neo4jClient, $qry, ":batchWriteSharedNotebooks", $params);
	}

	function handleQuery($neo4jClient, $qry, $endMsg, $params = array()) {
		$flag = 0;
		$msg = \Neo4jHelper::execute($neo4jClient, $qry, $params);
		if($msg==="OK")	{ $flag = 1; }

		return $flag;
	}

}


?>
