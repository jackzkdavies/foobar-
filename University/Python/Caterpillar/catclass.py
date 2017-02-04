import pygame

# Define some colors
black    = (   0,   0,   0)
white    = ( 255, 255, 255)
green    = (   0, 255,   0)
red      = ( 255,   0,   0)

class caterpillar:
    def __init__(self, x, y):
        self.face_xcoord = x
        self.face_ycoord = y
        self.body = segment_queue()

        
    def display_caterpillar(self, screen):
        self.draw_face(screen)
        self.draw_body(screen)

    def draw_face(self, screen):
        x = self.face_xcoord 
        y = self.face_ycoord
        pygame.draw.ellipse(screen,red,[x, y, 40, 45])
        pygame.draw.ellipse(screen,black,[x+6, y+10, 10, 15])
        pygame.draw.ellipse(screen,black,[x+24, y+10, 10, 15])
        pygame.draw.line(screen,black, (x+11, y), (x+9, y-10), 3)
        pygame.draw.line(screen,black, (x+24, y), (x+26, y-10), 3)
        
    def draw_body(self, screen):
        # traverse the segment queue
        current_node = self.body.head
        while current_node is not None:
           current_node.draw_segment(screen) 
           current_node = current_node.next 
       
#################################################
# you need to complete this method    

    def grow(self):
        if self.body.isEmpty():
            x = self.face_xcoord + 40
            y = self.face_ycoord
            self.body.addSegment(x, y)
        else:
            x = self.body.last.xcoord + 35
            y = self.body.last.ycoord
            self.body.addSegment(x, y)
        return
        # if body is empty new segment should be placed relative to head
        # call addSegment() method on self.body with correct location parameters
        # else find x and y coordinates for current last body segment
        # call addSegment() method on self.body with correct location parameters
    
    def moveLeft(self):
        x = self.face_xcoord
        self.face_xcoord = x-35
        
        
        current_node = self.body.head 
        while current_node is not None:
            x = current_node.xcoord
            current_node.xcoord = x -35
            current_node = current_node.next
            
            
        
        return
        
class segment_queue:
    def __init__(self):
        self.length = 0
        self.head = None
        self.last = None
      
    def isEmpty(self):
        return self.length == 0
      
#################################################
# you need to complete this method
      
    def addSegment(self, x, y):
        node = body_segment(x,y)
        if self.head == None:
            self.head= node
            self.last = node
        
        else:
            self.last.next = node
        self.last = node
        self.length += 1
        return 
        # create a new body_segment node, with parameters x and y     
        # if segment queue is empty, the new node is head and last          
        # else, find the last node and then append the new node 
        # increment length of the segment queue
 
  
class body_segment:
    def __init__(self, x, y):
        self.xcoord = x
        self.ycoord = y
        self.next = None
        
    def draw_segment(self, screen):
        x = self.xcoord
        y = self.ycoord
        pygame.draw.ellipse(screen,green,[x, y, 35, 40])
        pygame.draw.line(screen,black, (x+8, y+35), (x+8, y+45), 3)
        pygame.draw.line(screen,black, (x+24, y+35), (x+24, y+45), 3)
        
