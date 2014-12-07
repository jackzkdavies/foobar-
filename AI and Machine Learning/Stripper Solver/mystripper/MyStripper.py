#!/usr/bin/python
#This program is free software: you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation, either version 3 of the License, or
#(at your option) any later version.
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#For a copy of the license see .
 
#!/usr/bin/python
 
from string import *
 
class Stripper:
# A STRIPS-style planner for the blocks world
 
    ## Initialization code
    def __init__(self):
        """
        Initialize the global variables, and function dictionaries
        """
        
        self.worldStack =[]
        self.goalStack = []
        self.plan =[]
        
        """
        Variable used to during printing final plan for renaming the output to user set calls
        """
        self.newo=[]
        #token to function mappings
 
        #safety net tests are denoted by the following as the frst list item.
        self.SafetyTag = "SAFETYTAG"
 
    """
    Used to take user inputs and set to usable values
    """
    def setnamings(self,f,o):
        
        self.newo=o
        
        for i in self.worldStack:
            if i[0] == f[0]:
                i[0] = "onx"
            if i[0] == f[1]:
                i[0] = "notonx"
                
            if i[0] == f[2]:
                i[0] = "ony"
            if i[0] == f[3]:
                i[0] = "notony"
            
            if i[0] == f[4]:
                i[0] = "onz"
            if i[0] == f[5]:
                i[0] = "notonz"
                
            if i[0] == f[6]:
                i[0] = "clearx"
            
            if i[0] == f[7]:
                i[0] = "acty"
                
            if i[0] == f[8]:
                i[0] = "notacty"
                
        for i in self.goalStack:
            if i[0] == f[0]:
                i[0] = "onx"
            if i[0] == f[1]:
                i[0] = "notonx"
                
            if i[0] == f[2]:
                i[0] = "ony"
            if i[0] == f[3]:
                i[0] = "notony"
            
            if i[0] == f[4]:
                i[0] = "onz"
            if i[0] == f[5]:
                i[0] = "notonz"
                
            if i[0] == f[6]:
                i[0] = "clearx"
            
            if i[0] == f[7]:
                i[0] = "acty"
                
            if i[0] == f[8]:
                i[0] = "notacty"
                
        """
        the onx, y and z formulas are used to declare multiple 'platforms' eg ontable plate, onfloor couch.
        
        clearx is used in conjunction with actionx. the basic usage is to unstack  two items on each other 
        and stack item1 on a new item3. eg.
        
            on item1 item2          (on bullets gun)
            clearx item3 item1      (shoot duck bullets)
            
            final plan
            actionx item3 item1     (shot duck bullets )
            
                NOTE:the bullets are no longer on the gun
                
        acty and notacty are used to declare the state of something something and used such as 
        for example describing that a duck is on the water or that the ladder is not under the bananas and are used with the 
        applyacty and removeacty to preform this. 
        
            eg.
            acty item         (onwater duck)
            notacty item      (notontwater duck)
            
            Final Plan:
            removeacty item   (fly duck)
                NOTE: Duck no longer 'acty' or 'onwater'
      
        """ 
        self.formulas = { "holding" : self.holding, "armempty": self.armempty,
                  "on" : self.on , "clear": self.clear ,
                  
                  "onx" : self.onx,"notonx" : self.notonx, 
                  "ony": self.ony,"notony" : self.notony, 
                  "onz": self.onz,"notonz" : self.notonz, 
                  
                  "clearx":self.clearx,
                  
                  "acty":self.acty,
                  "notacty":self.notacty}
        
        """
        see above for use of new operators
        """
        self.Operators = {"STACK":self.stack,"UNSTACK":self.unstack,
                          
                          "PICKUPX":self.pickupx,"PUTDOWNX":self.putdownx,   
                          "PICKUPY":self.pickupy,"PUTDOWNY":self.putdowny,
                          "PICKUPZ":self.pickupz,"PUTDOWNZ":self.putdownz,
                          
                          "ACTIONX":self.actionx,
                          
                          "APPLYACTY":self.applyacty,
                          "REMOVEACTY":self.removeacty
                          }
    
    def populate(self,strng,stateStack):
        """
        Helper function that parses strng according to expectations
        and adds to the sateStack passed in.
        """
        for x in (strng.lower().replace('(','').replace(')','').split(',')):
            ls = x.strip().split(' ')
            stateStack.append(ls)
        stateStack.reverse()
 
    def populateGoal(self,strng):
        """
        Populate the goal stack with data in strng.
        """
        self.populate(strng,self.goalStack)
        # add original safety check
        goalCheck = []
        goalCheck.append(self.SafetyTag)
        for g in self.goalStack:
            goalCheck.append(g)
        self.goalStack.insert(0,goalCheck)
 
    def populateWorld(self,strng):
        """
        Populate the world state stack with data in strng.
        """
        self.populate(strng,self.worldStack)
 
    ## ----------------------------------------------------
    ##  Predicate logic atomic formula functions
    ##  All these functions:
    ##       Add the required operator,
    ##           the safety net,
    ##           and the preconditions for the operator
    ## ----------------------------------------------------
 
    def holding(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "holding"),"expected holding"
        assert (len(topG) == 2 ),"expected 2 for holding"
 
        #if on x, y, or z, add pickupx, y or z operator
        #if stacked, add unstack operator
 
        if (self.isonx(topG[1])):
            
            ## add operator pickupx
            self.goalStack.append(["PICKUPX", topG[1] ])
 
            #add safety net
            self.goalStack.append([self.SafetyTag, ["armempty"],
                                   ["onx", topG[1]],["clear", topG[1]]])
            #add the preconditions
            self.goalStack.append(["armempty"])
            self.goalStack.append(["onx", topG[1]])
            self.goalStack.append(["clear", topG[1]])
            
        elif (self.isony(topG[1])):
            
            ## add operator pickupx
            self.goalStack.append(["PICKUPY", topG[1] ])
 
            #add safety net
            self.goalStack.append([self.SafetyTag, ["armempty"],
                                   ["ony", topG[1]],["clear", topG[1]]])
            #add the preconditions
            self.goalStack.append(["armempty"])
            self.goalStack.append(["ony", topG[1]])
            self.goalStack.append(["clear", topG[1]])
            
        elif (self.isonz(topG[1])):
            
            ## add operator pickupx
            self.goalStack.append(["PICKUPZ", topG[1] ])
 
            #add safety net
            self.goalStack.append([self.SafetyTag, ["armempty"],
                                   ["onz", topG[1]],["clear", topG[1]]])
            #add the preconditions
            self.goalStack.append(["armempty"])
            self.goalStack.append(["onz", topG[1]])
            self.goalStack.append(["clear", topG[1]])
        else:
            #add UNSTACK,
            y = self.getItemUnder(topG[1]) # get y from world, i.e w/e's under topG[1]
            if y ==  0 :
                raise Exception, "Nothing is under " + topG[1]
            self.goalStack.append(["UNSTACK",topG[1],y])
 
            #add safety check
            self.goalStack.append([self.SafetyTag, ["armempty"],
                                  ["clear", topG[1]],["on", topG[1],y]])
 
            #add the preconditions
            self.goalStack.append(["armempty"])
            self.goalStack.append(["clear", topG[1]])
            self.goalStack.append(["on",topG[1],y])

    def armempty(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "armempty"),"expected armempty"
        assert (len(topG) == 1 ),"expected 1 for armempty"
        ## add operator  put down
        x = self.getHoldingItem()   #get x from world state
        #i.e get whatever variable is being held
        if ( x == 0 ):
            raise Exception, " No item being held, this is a problem. Exiting."
        self.goalStack.append(["PUTDOWNX", x])
 
        #add safety check
        self.goalStack.append([self.SafetyTag, ["holding",x]])
 
        #add preconditions
        self.goalStack.append(["holding",x])
 
    def on(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "on"),"expected on"
        assert (len(topG) == 3 ),"expected 3 for on"
        ## add operator assumes not in world state
        #add STACK,
        self.goalStack.append(["STACK",topG[1],topG[2] ])
 
        #add safety check
        self.goalStack.append([self.SafetyTag,
                               ["holding", topG[1]],["clear", topG[2]]])
 
        #add the preconditions
        self.goalStack.append(["holding", topG[1]])
        self.goalStack.append(["clear", topG[2]])
 
    def clear(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "clear"),"expected clear"
        assert (len(topG) == 2 ),"expected 2 for clear"
        ## add the preconditions to the goalstack.
        #add UNSTACK,
        x = self.getItemOn(topG[1]) # get x from world, i.e w/e's on top of topG[1]
        if x ==  0 :
            raise Exception, "Nothing is on " + topG[1]
        self.goalStack.append(["UNSTACK", x, topG[1]])
 
        #add safety check
        self.goalStack.append([self.SafetyTag, ["armempty"],
                               ["clear", x],["on", x, topG[1]]])
 
        #add the preconditions
        self.goalStack.append(["armempty"])
        self.goalStack.append(["clear", x])
        self.goalStack.append(["on", x,topG[1]])
 
    def onx(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "onx"),"expected onx"
        assert (len(topG) == 2 ),"expected 2 for onx"
        ## add the preconditions to the goalstack.
 
        ## add putdownx
        self.goalStack.append(["PUTDOWNX", topG[1]])
        #add safety check
        self.goalStack.append([self.SafetyTag,["holding", topG[1]]])
 
        #add preconditions
        self.goalStack.append(["holding",topG[1]])
        
    def ony(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "ony"),"expected ony"
        assert (len(topG) == 2 ),"expected 2 for ony"
        ## add the preconditions to the goalstack.
 
        ## add under
        self.goalStack.append(["PUTDOWNY",topG[1]])
        #add safety check
        self.goalStack.append([self.SafetyTag,["holding", topG[1]]])
 
        #add preconditions
        self.goalStack.append(["holding",topG[1]])
        
    def onz(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "onz"),"expected onz"
        assert (len(topG) == 2 ),"expected 2 for ony"
        ## add the preconditions to the goalstack.
 
        ## add under
        self.goalStack.append(["PUTDOWNZ",topG[1]])
        #add safety check
        self.goalStack.append([self.SafetyTag,["holding", topG[1]]])
 
        #add preconditions
        self.goalStack.append(["holding",topG[1]])

    def notonx(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "notonx"),"expected notonx"
        assert (len(topG) == 2 ),"expected 2 for clear"
        ## add the preconditions to the goalstack.
        
        x = self.getItemOn(topG[1]) # get x from world, i.e w/e's on top of topG[1]
        if x ==  0 :
            print "nothing on " + topG[1]
        else:   
            print x
            self.goalStack.append(["UNSTACK", x, topG[1]])
            
        self.goalStack.append(["PICKUPX",topG[1]])   

 
        #add the preconditions
        self.goalStack.append(["armempty"])
        self.goalStack.append(["onx",topG[1]])
        
    def notony(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "notony"),"expected notony"
        assert (len(topG) == 2 ),"expected 2 for clear"
        ## add the preconditions to the goalstack.
        
        x = self.getItemOn(topG[1]) # get x from world, i.e w/e's on top of topG[1]
        if x ==  0 :
            print "nothing on " + topG[1]
        else:   
            self.goalStack.append(["UNSTACK", x, topG[1]])
            
        self.goalStack.append(["PICKUPY",topG[1]])   

 
        #add the preconditions
        self.goalStack.append(["armempty"])
        self.goalStack.append(["ony",topG[1]])
        
    def notonz(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "notonz"),"expected notonz"
        assert (len(topG) == 2 ),"expected 2 for clear"
        ## add the preconditions to the goalstack.
        
        x = self.getItemOn(topG[1]) # get x from world, i.e w/e's on top of topG[1]
        if x ==  0 :
            print "nothing on " + topG[1]
        else:   
            self.goalStack.append(["UNSTACK", x, topG[1]])
            
        self.goalStack.append(["PICKUPZ",topG[1]])   

 
        #add the preconditions
        self.goalStack.append(["armempty"])
        self.goalStack.append(["onz",topG[1]])

    def clearx(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "clearx"),"expected clearx"
        assert (len(topG) == 3 ),"expected 3 for clearx"
        ## add the preconditions to the goalstack.
 
        self.goalStack.append(["ACTIONX",topG[1],topG[2]])   

        #add the preconditions
        self.goalStack.append(["notonx",topG[1]])
        self.goalStack.append(["on", 'bullets','gun'])    
        
    def acty(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "acty"),"expected acty"
        assert (len(topG) == 2 ),"expected 2 for acty"
        ## add the preconditions to the goalstack.
        
        self.goalStack.append(["PUTDOWNX", topG[1]])
        self.goalStack.append(["APPLYACTY",topG[1]])
        #add safety check
        self.goalStack.append([self.SafetyTag,["holding", topG[1]]])
 
        #add preconditions
        self.goalStack.append(["holding",topG[1]])
        
    def notacty(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "notacty"),"expected notacty"
        assert (len(topG) == 2 ),"expected 3 for notacty"
        ## add the preconditions to the goalstack.
        
        x = self.getItemOn(topG[1]) # get x from world, i.e w/e's on top of topG[1]
        if x ==  0 :
            print "nothing on " + topG[1]
        else:   
            self.goalStack.append(["UNSTACK", x, topG[1]])
            
        self.goalStack.append(["PICKUPX", topG[1]])
        self.goalStack.append(["REMOVEACTY",topG[1]])   

        #add the preconditions
        self.goalStack.append(["armempty"])
        self.goalStack.append(["acty",topG[1]])
        
    
 
    ## ----------------------------------------------------
    ## Functions that modify the world
    ##     All these functions:
    ##       Delete and add states to the world state stack
    ##       as required by their corresponding operator
    ## ----------------------------------------------------
 
    def stack(self,subgoal):
        #deletions
        self.worldStateRemove(["clear",subgoal[2]])
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["armempty"])
        self.worldStateAdd(["on",subgoal[1],subgoal[2]])
        
    def actionx(self,subgoal):
        #deletions
        x = self.getItemUnder(subgoal[2])
        self.worldStateRemove(["on",subgoal[2], x])
        #addition
        self.worldStateAdd(["armempty"])
        self.worldStateAdd(["clearx",subgoal[1],subgoal[2]])     
        
    def applyacty(self,subgoal):
        #deletions
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["acty",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
    def removeacty(self,subgoal):
        self.worldStateRemove(["acty",subgoal[1]])
        self.worldStateRemove(["holding",subgoal[1]])
        
        #addition
        self.worldStateAdd(["notacty",subgoal[1]])
        self.worldStateAdd(["armempty"])
 
    def unstack(self,subgoal):
        #deletions
        self.worldStateRemove(["on",subgoal[1],subgoal[2]])
        self.worldStateRemove(["armempty"])
        #addition
        self.worldStateAdd(["holding",subgoal[1]])
        self.worldStateAdd(["clear",subgoal[2]])
 
    def pickupx(self,subgoal):
        #deletions
        self.worldStateRemove(["onx",subgoal[1]])
        self.worldStateRemove(["armempty"])
        #addition
        self.worldStateAdd(["notonx",subgoal[1]])
        self.worldStateAdd(["holding",subgoal[1]])
 
    def putdownx(self,subgoal):
        #deletions
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["onx",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
        
    def pickupy(self,subgoal):
        #deletions
        self.worldStateRemove(["ony",subgoal[1]])
        self.worldStateRemove(["holding",subgoal[1]])
        
        #addition
        self.worldStateAdd(["notony",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
    def putdowny(self,subgoal):
        #deletions
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["ony",subgoal[1]])
        self.worldStateAdd(["armempty"])
    
    def pickupz(self,subgoal):
        #deletions
        self.worldStateRemove(["onz",subgoal[1]])
        self.worldStateRemove(["holding",subgoal[1]])
        
        #addition
        self.worldStateAdd(["notonz",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
    def putdownz(self,subgoal):
        #deletions
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["onz",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
    ## ----------------------------------------------------
    ## Solver
    ##   Attempts to solve the problem using the setup
    ##   goal and world states
    ## ----------------------------------------------------
 
    def solve(self):
        """ Attempts to solve the problem using STRIPS Algorithm
        Note: You need to setup the problem prior to running this
              by using populateWorld and populateGoal using a well
              formatted string.
              
              ANDD setting the input and output names using setnamings()
        """
        if (not len(self.worldStack) > 0) or (not len(self.goalStack) > 0):
            print "\nNothing to do.\nMake sure you populate the problem using\npopulateWorld and populateGoal before calling this function."
            return
        while len(self.goalStack) > 0 :
            #if the subgoal is in world state
            if self.top(self.goalStack) in self.worldStack:
                # pop it from the stack
                subgoal = self.goalStack.pop()
            #if that item is an operator,
            elif (self.Operators.has_key( self.top(self.goalStack)[0].upper())):
                subgoal = self.goalStack.pop()
                #store it in a "plan"
                self.plan.append(subgoal)
                # and modify the world state as specified
                self.Operators[(subgoal[0])](subgoal)
            #if the item is a safety check
            elif (self.SafetyTag == self.top(self.goalStack)[0].upper()):
                safetyCheck = self.goalStack.pop()
                for check in safetyCheck[1:]:
                    if not (check in self.worldStack):
                        print " Safety net ripped.n Couldn't construct a plan. Exiting...",check
                        return
            else:
                #find an operator that will cause the
                #top subgoal to result
                if (self.formulas.has_key(self.top(self.goalStack)[0])):
                    self.formulas[self.top(self.goalStack)[0]]()
                else:
                    raise Exception, self.top(self.goalStack)[0] + " not valid formula/subgoal"
                #or add to goal stack and try, but not doing that for now.
                
        """
        edited to update the output to set operator names
        """
        
        print "\nFinal Plan:\n" ,
        for step in self.plan :
            if step[0] == "PICKUPX":
                step[0] = self.newo[0]
            if step[0] == "PUTDOWNX": 
                step[0] = self.newo[1] 
            if step[0] ==  "PICKUPY":
                step[0] = self.newo[2]
            if step[0] == "PUTDOWNY":
                step[0] = self.newo[3]
            if step[0] == "PICKUPZ":
                step[0] = self.newo[4]
            if step[0] == "PUTDOWNZ":
                step[0] = self.newo[5]
            
            if step[0] ==  "ACTIONX":
                step[0] = self.newo[6]
            if step[0] ==   "APPLYACTY":
                step[0] = self.newo[7]
            if step[0] ==  "REMOVEACTY":
                step[0] = self.newo[8]
                
            print "  " ,join(step," ").upper()
 
    ## ----------------------------------------------------
    ## Utility functions
    ## ----------------------------------------------------
 
    def getItemUnder(self,item):
        """
        Returns the item that is on the passed in item in the world state.
        Returns 0 if no such item exists
        """
        for x in self.worldStack:
            if x[0] == "on" and x[1] == item :
                return x[2]
        return 0


    def isonx(self, item):
        """
        Returns true if the item is onx
        """
        return (["onx",item] in self.worldStack)
    
    def isony(self, item):
        """
        Return true if is on y
        """
        
        return (["ony",item] in self.worldStack)
    
    def isonz(self, item):
        """
        Returns true if is on z
        """
        return (["onz",item] in self.worldStack)

    def isnotonx(self, item):
        """
        Return true if not on x
        """
        
        return (["notonx",item] in self.worldStack)

    def isnotony(self, item):
        """
        Return true if not on y
        """
        
        return (["notony",item] in self.worldStack)
    
    def isnotonz(self, item):
        """
        Return true if not on z
        """
        
        return (["notonz",item] in self.worldStack)
        
    def getHoldingItem(self):
        """
        Returns the item that is being held in the world state.
        Returns 0 if no such item exists
        """
        for x in self.worldStack:
            if x[0] == "holding":
                return x[1]
        return 0
 
    def getItemOn(self, item):
        """
        Returns the item that is on the passed in item in the world state.
        Returns 0 if no such item exists
        """
        for x in self.worldStack:
            if x[0] == "on" and x[2] == item :
                return x[1]
        return 0

    def worldStateAdd(self,toAdd):
        """
        Adds a state to world state if the state isn't already true
        """
        if toAdd not in self.worldStack:
            self.worldStack.append(toAdd)
 
    def worldStateRemove(self, toRem):
        """
        Tries to remove the toRem state from the world state stack.
        """
        while toRem in self.worldStack:
            self.worldStack.remove(toRem)
 
    def top(self,lst):
        """
        Returns the item at the end of the given list
        We dont catch an error ecause thats the error we want it to throw.
        """
        return lst[len(lst) -1 ]
 
def runTests():
    """
    Test function to run all tests provided with project requirement
    documentation and a few more.
    
    default namings example
        s.setnamings(['onx','notonx','ony','notony','onz','notonz','clearx','acty','notacty'],
                 ['pickupx','putdownx','pickupy','putdowny','pickupz','putdownz','actionx','applyacty','removeacty'])
                 
    """
    print "\n\nRunning Test 1\n"
    ws = "((clear bullets), (clear gun), (ontable bullets), (ontable gun), (onwater duck), (armempty)) "
    gs1 = "((notonwater duck), (on bullets gun), (clear bullets), (armempty))"
 
    s = Stripper()
     
    s.populateGoal(gs1)
    s.populateWorld(ws)
     
    s.setnamings(['ontable','notontable',' ',' ','onwater','notonwater',' ',' ',' '],
                 ['pickupfromtable','putdownontable',' ',' ','duckfly','duckswim',' ',' ',' '])
     
    s.solve()
    print "\n\nRunning Test 2\n"
    gs1 = "((notonwater duck), (on bullets gun), (clear bullets), (armempty))"
    gs2 = "((shoot duck bullets))"
    s2 = Stripper()
    s2.populateGoal(gs2)
    s2.populateWorld(gs1)
    s2.setnamings(['onwater','notonwater',' ',' ',' ',' ','shoot',' ',' '],
                 [' ',' ',' ',' ',' ',' ','shot',' ',' '])
    s2.solve()
    
    print "\n\nRunning Test 3\n"
    ws = "((clear Ladder), (armempty), (onfloor Couch),  (onfloor Ladder), (on Monkey Couch), (clear Monkey), (bellowbananas Couch),(notbellowbananas Ladder)) "
    gs = "((clear Couch), (notbellowbananas Couch), (bellowbananas Ladder),(on Monkey Ladder))"
    s3 = Stripper()
    s3.populateGoal(gs)
    s3.populateWorld(ws)
    s3.setnamings(['onfloor','notonfloor',' ',' ',' ',' ',' ','bellowbananas','notbellowbananas'],
             ['pickupfromfloor','putdownonfloor',' ',' ',' ',' ',' ','movedunderbananas','movefromunderbananas'])
    s3.solve()
 
  
if __name__ == "__main__" :
    runTests()
