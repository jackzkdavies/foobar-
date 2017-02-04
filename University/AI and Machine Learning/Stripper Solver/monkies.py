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
 
        #token to function mappings
        """
        The added formulas of 'bellowbananas' and 'notbellowbananas' are used to declear if a single item is or isnt under the bananas, 
        more then on item ca n be under the bananas but each call only takes on item at a time like the clear function
        """
        self.formulas = { "holding" : self.holding, "armempty": self.armempty,
                          "on" : self.on , "clear": self.clear ,
                          "onfloor" : self.onfloor, 
    
                          "bellowbananas": self.bellowbananas,"notbellowbananas" : self.notbellowbananas, }
        
        
        """
        The added 'underbananas' and 'clearbananas' is used in conjunction with the bellow and notbellow bananas formulas,
        they are the operators that place an item 'bellowbananas' or remove's an item from bellowbananas('notbellowbananas')
        """
        self.Operators = {"STACK":self.stack,"UNSTACK":self.unstack,
                          "PICKUP":self.pickup,"PUTDOWN":self.putdown,
                          
                          "UNDERBANANAS":self.underbananas, "CLEARBANANAS":self.clearbananas}
 
        #safety net tests are denoted by the following as the frst list item.
        self.SafetyTag = "SAFETYTAG"
 
    def populate(self,strng,stateStack):
        """
        Helper function that parses string according to expectations
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
 
        #if on table, add pickup operator
        #if stacked, add unstack operator
 
        if (self.isonfloor(topG[1])):
            
            ## add operator pickup
            self.goalStack.append(["PICKUP", topG[1] ])
 
            #add safety net
            self.goalStack.append([self.SafetyTag, ["armempty"],
                                   ["onfloor", topG[1]],["clear", topG[1]]])
            #add the preconditions
            self.goalStack.append(["armempty"])
            self.goalStack.append(["onfloor", topG[1]])
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
        self.goalStack.append(["PUTDOWN", x])
 
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
 
    def onfloor(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "onfloor"),"expected onfloor"
        assert (len(topG) == 2 ),"expected 2 for onfloor"
        ## add the preconditions to the goalstack.
 
        ## add putdown
        self.goalStack.append(["PUTDOWN", topG[1]])
        #add safety check
        self.goalStack.append([self.SafetyTag,["holding", topG[1]]])
 
        #add preconditions
        self.goalStack.append(["holding",topG[1]])
       
    """
    The bellowbanans function takes an item being help and places it under the bananas 
    """   
       
        
    def bellowbananas(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "bellowbananas"),"expected bellowbananas"
        assert (len(topG) == 2 ),"expected 2 for bellowbananas"
        ## add the preconditions to the goalstack.
 
        ## add under
        self.goalStack.append(["PUTDOWN", topG[1]])
        self.goalStack.append(["UNDERBANANAS",topG[1]])
        #add safety check
        self.goalStack.append([self.SafetyTag,["holding", topG[1]]])
 
        #add preconditions
        self.goalStack.append(["holding",topG[1]])
        
    """
    The notbellowbanans makes sure an item is not bellow the bananas  
    """     
        
    def notbellowbananas(self):
        topG = self.top(self.goalStack)
        assert (topG[0] == "notbellowbananas"),"expected notbellowbananas"
        assert (len(topG) == 2 ),"expected 2 for clear"
        ## add the preconditions to the goalstack.
        
        x = self.getItemOn(topG[1]) # get x from world, i.e w/e's on top of topG[1]
        if x ==  0 :
            print "nothing on " + topG[1]
        else:   
            print x
            self.goalStack.append(["UNSTACK", x, topG[1]])
            
        self.goalStack.append(["PICKUP", topG[1]])
        self.goalStack.append(["CLEARBANANAS",topG[1]])   

 
        #add the preconditions
        self.goalStack.append(["armempty"])
        self.goalStack.append(["bellowbananas",topG[1]])
        
        
        
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
 
    def unstack(self,subgoal):
        #deletions
        self.worldStateRemove(["on",subgoal[1],subgoal[2]])
        self.worldStateRemove(["armempty"])
        #addition
        self.worldStateAdd(["holding",subgoal[1]])
        self.worldStateAdd(["clear",subgoal[2]])
 
    def pickup(self,subgoal):
        #deletions
        self.worldStateRemove(["onfloor",subgoal[1]])
        self.worldStateRemove(["armempty"])
        #addition
        self.worldStateAdd(["holding",subgoal[1]])
 
    def putdown(self,subgoal):
        #deletions
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["onfloor",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
        
        
    """
    Takes what you are holding and places under the bananas 
    """   
        
    def underbananas(self,subgoal):
        #deletions
        self.worldStateRemove(["holding",subgoal[1]])
        #addition
        self.worldStateAdd(["bellowbananas",subgoal[1]])
        self.worldStateAdd(["armempty"])
        
    """
    If the subgoal is bellow the bananas it will move it to being not bellow the bananas 
    """
    def clearbananas(self,subgoal):
        #deletions
        self.worldStateRemove(["bellowbananas",subgoal[1]])
        self.worldStateRemove(["holding",subgoal[1]])
        
        #addition
        self.worldStateAdd(["notbellowbananas",subgoal[1]])
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
                        print " Safety net ripped.n Couldn't contruct a plan. Exiting...",check
                        return
            else:
                #find an operator that will cause the
                #top subgoal to result
                if (self.formulas.has_key(self.top(self.goalStack)[0])):
                    self.formulas[self.top(self.goalStack)[0]]()
                else:
                    raise Exception, self.top(self.goalStack)[0] + " not valid formula/subgoal"
                #or add to goal stack and try, but not doing that for now.
        print "\nFinal Plan:\n" ,
        for step in self.plan :
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

    def isonfloor(self, item):
        """
        Returns true if the item is on the table.
        False otherwise.
        """
        return (["onfloor",item] in self.worldStack)
    
    def isbellowbananas(self, item):
        """
        Return true if bellow bananas
        """
        
        return (["bellowbananas",item] in self.worldStack)
    
    def isnotbellowbananas(self, item):
        """
        Return true if not bellow bananas
        """
        
        return (["notbellowbananas",item] in self.worldStack)
        
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
    The Monkey problem.
    Somewhere in the room is a bunch of bananas, however they are out of reach for the Monkey to grab unless he is on top of a ladder
    and the ladder is bellow the bananas.
    The follow are examples to place the monkey bellow the bananas on top of the ladder.
    
    Use the follows methods to disscribe the world state and the goal state for the monkey
        'clear item' -the item is 'clear', avaliable to be picked up.
        'onfloor item' -a description the the item is on the floor.
        'bellowbananas item' -declare that an item is bellow the bananas.
        'notbellowbananas item' -declare that an item is NOTbellow the bananas.
        'on item1 item2' declare that item1 is on top of item2, item1 should be clear and item2 should be on the floor. 
    """
    
    
    print "\n\nRunning Test 1\n"
    ws = "((clear Ladder), (onfloor Couch), (onfloor Ladder), (clear Monkey), (clear couch), (onfloor Monkey), (bellowbananas Couch), (notbellowbananas Ladder), (armempty)) "
    gs = "((notbellowbananas Couch), (bellowbananas Ladder),(on Monkey Ladder))"
    s = Stripper()
    s.populateGoal(gs)
    s.populateWorld(ws)
    s.solve()


#    ## Test 3
    print "\n\nRunning Test 2\n"
    ws = "((clear Ladder), (armempty), (onfloor Couch),  (onfloor Ladder), (on Monkey Couch), (clear Monkey), (bellowbananas Couch), (notbellowbananas Ladder)) "
    gs = "((clear Couch), (notbellowbananas Couch), (bellowbananas Ladder), (on Monkey Ladder))"
    s = Stripper()
    s.populateGoal(gs)
    s.populateWorld(ws)
    s.solve()
  
if __name__ == "__main__" :
    runTests()
