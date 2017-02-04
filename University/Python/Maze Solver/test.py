import pygame
import mazeclass

# Initialize pygame
pygame.init()
  
# Set the height and width of the screen
size=[1000,500]
screen=pygame.display.set_mode(size)
 
# Set title of screen
pygame.display.set_caption("Maze Project")

# Get a new maze
mazegrid =  [[2,2,2,2,2,2,2,6,2,2,2,2,2,2,2,2,2,2,2,2],
             [2,0,0,0,0,1,0,0,1,0,0,1,0,0,1,0,0,0,0,2],
             [2,0,1,1,1,1,0,0,1,0,0,1,0,1,1,1,1,1,0,2],
             [2,0,0,0,0,1,0,0,1,0,0,1,0,0,0,0,0,0,0,2],
             [2,1,1,1,0,0,0,1,1,1,0,1,1,1,1,1,0,1,1,2],
             [2,0,0,0,0,1,0,0,0,0,0,0,0,0,1,0,0,0,0,2],
             [2,0,1,1,0,1,1,1,1,0,1,1,1,1,1,0,1,0,0,2],
             [2,0,1,0,0,1,0,0,0,0,0,0,0,1,0,0,1,1,0,2],
             [2,0,1,0,0,1,0,1,0,1,0,1,0,0,0,0,1,0,0,2],
             [2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2]]

the_maze = mazeclass.Maze(mazegrid)


##########################################################

# code to be implemented

def refresh():
    the_maze.display_maze(screen)
    pygame.time.delay(50)
    pygame.display.flip()
    pygame.time.Clock().tick(100)    

def dfs(x,y):    
    refresh() 
    
    the_maze.bot_xcoord = x
    the_maze.bot_ycoord = y
    neighbours = [(x+1, y), (x-1, y), (x, y+1),(x, y-1)]
    
    for i,j in neighbours:
        if the_maze.grid[i][j].status == 0:
                the_maze.grid[x][y].status = 3
                dfs(i,j)
                
    for i,j in neighbours:
        if the_maze.grid[i][j].status == 3:
                the_maze.grid[x][y].status = 4
                dfs(i,j)

    
def setpath(x,y, stack=[]):
    
    neighbours = [(x+1, y) , (x-1, y) , (x, y+1), (x, y-1)]

    if the_maze.grid[x][y].status == 6:
        print "Found Exit"
        stack.append((x,y))
        while stack != []:
            print stack
            refresh()
            position = stack.pop(0)
            the_maze.bot_xcoord = position[0]
            the_maze.bot_ycoord = position[1]
            the_maze.grid[the_maze.bot_xcoord][the_maze.bot_ycoord].status = 3
    
    
    the_maze.grid[x][y].status = 5
    children = [(a,b) for (a,b) in neighbours if the_maze.grid[a][b].status in [0,6]]
    
    if children != []:   
#        print stack
        stack.append((x,y))
        i,j = children[0]
        setpath(i,j, stack)
    elif stack != []:
        i,j = stack.pop()
        setpath(i,j, stack)
    
def test(neighbours,n):

        
    hold = neighbours[0]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[0] = hold2
    if n == 1:
        return neighbours

    
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 2:
        return neighbours     
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 3:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 4:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 5:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 6:
        return neighbours

#    *********************
    hold = neighbours[0]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[0] = hold2
    if n == 7:
        return neighbours

    
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 8:
        return neighbours
    
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 9:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 10:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 11:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 12:
        return neighbours

#    *********************
    hold = neighbours[0]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[0] = hold2
    if n == 13:
        return neighbours

    
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 14:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 15:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 16:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 17:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 18:
        return neighbours
    
#    *********************
    
    hold = neighbours[0]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[0] = hold2
    if n == 19:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 20:
        return neighbours
    
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 21:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 22:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    if n == 23:
        return neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    if n == 24:
        return neighbours
    
    
       
def shortestpath(x,y,stack=[]):
    
    neighbours = [(x+1, y) , (x-1, y) , (x, y+1), (x, y-1)]
    for n in range (1,25):
        neighbours2 = test(neighbours,n)
    

        if the_maze.grid[x][y].status == 6:
            print "Found Exit Qucikest"
            print len(stack)
            stack.append((x,y))
            while stack != []:
                refresh()
                position = stack.pop(0)
                the_maze.bot_xcoord = position[0]
                the_maze.bot_ycoord = position[1]
                the_maze.grid[the_maze.bot_xcoord][the_maze.bot_ycoord].status = 3
        
        
        the_maze.grid[x][y].status = 5
        children = [(a,b) for (a,b) in neighbours if the_maze.grid[a][b].status in [0,6]]
        
        if children != []:   
            stack.append((x,y))
            i,j = children[0]
            shortestpath(i,j, stack)
        elif stack != []:
            i,j = stack.pop()
            shortestpath(i,j, stack)

            

# Loop until the user clicks the close button.
done=False

# Used to manage how fast the screen updates
clock=pygame.time.Clock()

######################################
# -------- Main Program Loop -----------
while done==False:
        for event in pygame.event.get(): # User did something
            if event.type == pygame.QUIT: # If user clicked close
                done=True # Flag that we are done so we exit this loop
            if event.type == pygame.KEYDOWN: # If user wants to perform an action
                if event.key == pygame.K_d:
                    the_maze.reset(mazegrid)
                    dfs(the_maze.bot_xcoord, the_maze.bot_ycoord)
                if event.key == pygame.K_s:
                    the_maze.reset(mazegrid)
                    setpath(the_maze.bot_xcoord, the_maze.bot_ycoord)
                if event.key == pygame.K_q:
                    the_maze.reset(mazegrid)
                    shortestpath(the_maze.bot_xcoord, the_maze.bot_ycoord)
                if event.key == pygame.K_t:
                    the_maze.reset(mazegrid)
                    test()
     
        the_maze.display_maze(screen)
        # Limit to 20 frames per second
        clock.tick(50)
 
        # Go ahead and update the screen with what we've drawn.
        pygame.display.flip()
     
# If you forget this line, the program will 'hang' on exit.
pygame.quit ()