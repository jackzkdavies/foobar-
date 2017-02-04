# Template for the algorithm to solve a sudoku. Builds a recursive backtracking solution
# that branches on possible values that could be placed in the next empty cell. 
# Initial pruning of the recursion tree - 
#       we don't continue on any branch that has already produced an inconsistent solution
#       we stop and return a complete solution once one has been found

import pygame, Sudoku_IO, random

restart = None
cloned = False
                                              
def solve(snapshot, screen):
    # display current snapshot
    pygame.time.delay(20)
    Sudoku_IO.displayPuzzle(snapshot, screen)
    pygame.display.flip()

    global restart, cloned
    numbers = [1,2,3,4,5,6,7,8,9]
    
    # makes list of list with values of what is each cell by rows, cols and blocks.
    rowList = [[],[],[],[],[],[],[],[],[],]
    colList = [[],[],[],[],[],[],[],[],[],]
    blockList = [[],[],[],[],[],[],[],[],[],]
   
    for r in range(0,9):
        for i in range(0,9):
            x = snapshot.cellsByRow(r)
            rowList[r].append(x[i].val)
            x = snapshot.cellsByCol(r)
            colList[r].append(x[i].val) 
            
    hold = []
    for x in range (0,9,3):
        for y in range(0,9,3):
            for i in range(0,9):
                u = snapshot.cellsByBlock(x,y)
                hold.append(u[i].val)           
    for x in range (0,9):
        for y in range (0,9):
            blockList[x].append(hold[y + x*9])
    
    # checks what is in left to go in each row, col and block
    todoRow = [[],[],[],[],[],[],[],[],[],]
    todoCol = [[],[],[],[],[],[],[],[],[],]
    todoBlock = [[],[],[],[],[],[],[],[],[],]
    
    for t in range (0,9):
        temp = rowList[t]
        temp2 = colList[t]
        temp3 = blockList[t]
        todoRow[t] = [x for x in numbers if x not in temp]
        todoCol[t] = [x for x in numbers if x not in temp2]
        todoBlock[t] = [x for x in numbers if x not in temp3]
    
    #Looks at each cell, makes a possibilities dictionary with cell locations as keys (x,y) 
    #with a list of possible solutions for that cell as the keys values.
    posDict = {}
    for x in range(9):
        for y in range(9):
            cell = snapshot.getCellVal(x,y)
            if cell == 0:
                if x in [0,1,2] and y in [0,1,2] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[0]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})
                if x in [0,1,2] and y in [3,4,5] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[1]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})                   
                if x in [0,1,2] and y in [6,7,8] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[2]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})
                    
                    
                if x in [3,4,5] and y in [0,1,2] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[3]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})
                if x in [3,4,5] and y in [3,4,5] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[4]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2}) 
                if x in [3,4,5] and y in [6,7,8] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[5]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})
                    
                if x in [6,7,8] and y in [0,1,2] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[6]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})
                if x in [6,7,8] and y in [3,4,5] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[7]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})  
                if x in [6,7,8] and y in [6,7,8] :
                    hold= todoRow[x]
                    hold2= todoCol[y]
                    hold3= todoBlock[8]
                    name = str(x) + str(y) 
                    temp = [t for t in hold if t in hold2]
                    temp2 = [t for t in temp if t in hold3]
                    posDict.update({name: temp2})
                
                   
    #looks over dictionary for cells with only one possible solution and applies that 
    #solution 
    singletons = []
    for items in posDict:
        if len(posDict[items]) ==1:
            singletons.append(1)
    
    
    if len(singletons) > 0:                  
        for items in posDict:
            if len(posDict[items]) == 1:
                items = str(items)
                x= int(items[0])
                y= int(items[1])
                solution = int(posDict[items][0])
                snapshot.setCellVal(x,y,solution)
                solve(snapshot,screen)
                
    #makes a copy of the sudoku with all completed singletons as a restart point             
    if cloned == False:
        restart = snapshot.clone()
        cloned = True  
    
    #if there is more then one possible cell solution, randomly pick one, If possibilities
    # dictionary contains a key with no possible solutions, this indicates a randomly chosen 
    #cell number was inconsistent with sudoku rules, go back and try again from point where
    #singletons have been done, try a new random path to solution. 


    ####### For a reason I could not figure out, my program will go back and try one time
    ####### but if it doesn't find the solution the second time it will get stuck in a 
    ####### loop not retrying. Comments on why this is would be helpful. 

    if isComplete(snapshot) == False:
        for items in posDict:
            length = len(posDict[items])
            if length > 0 :
                rand = random.randrange(0,length)        
                items = str(items)
                x= int(items[0])
                y= int(items[1])             
                solution = int(posDict[items][rand])
                snapshot.setCellVal(x,y,solution)    
                solve(snapshot,screen)
            else:
                break    
        print "Incorrect, retry"
        solve(restart,screen)
 
     
def checkConsistency(snapshot):
    return True


    # Check whether a puzzle is solved. 
    # return true if the sudoku is solved, false otherwise
def isComplete(snapshot):
    if len(snapshot.unsolvedCells()) ==0:
        print "Is Complete"
        return True
        
    else:
        print "Is Not Complete"
        return False
