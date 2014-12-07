
#include <windows.h> 
#include <GL/glut.h> 
#include <time.h>

char title[] = "3D Shapes";
 
struct cylinder
{
    GLfloat pos[3];
    GLfloat radius;
    GLfloat height;
    GLfloat angle; // rotation around z axis
    GLfloat color[4];
};

typedef struct cylinder CYLINDER;

#define MAX_LAYER 3
CYLINDER layers[MAX_LAYER] = {
    {{0.0f, 0.15f, -1.0f}, 0.2,0.0, 0, {0.0, 0.5, 1, 1}},
	{{0.0f, 0.1f, -1.0f}, 0.1, 0.0, 0, {0.0, 0.5, 1, 1}},
	{{0.0f, 0.05f, -1.0f}, 0.05, 0.0, 0, {0.0, 0.5, 1, 1}}
};

CYLINDER candle[MAX_LAYER] = {
    {{-0.15f, -0.1f, -0.4f}, 0.005,0.025, 0, {1.0, 0.5, 1, 1}},
	
};

void sleep(unsigned int mseconds)
{
    clock_t goal = mseconds + clock();
    while (goal > clock());
}

void drawCake(CYLINDER *cylinder, float y )
{
	glLoadIdentity();                 // Reset the model-view matrix
	glTranslatef(cylinder->pos[0],y-(cylinder->pos[1]),cylinder->pos[2]);  // Move right and into the screen
   
	glMaterialfv(GL_FRONT, GL_AMBIENT_AND_DIFFUSE, cylinder->color);
   
   
	glRotatef(90,1,0,0);
 
	GLUquadricObj *quad= gluNewQuadric();
	gluCylinder(quad,cylinder->radius,cylinder->radius,cylinder->height+y,100,1);

	//top circle
	glRotatef(180,1,0,0);
	gluDisk(quad, 0,cylinder->radius,100,1);

	//bottom circle need to fix
	glTranslatef(cylinder->pos[0],cylinder->pos[1],cylinder->pos[2]);
	gluDisk(quad, 0,cylinder->radius,100,1);
      
	gluDeleteQuadric(quad);
}

void drawCandle(CYLINDER *cylinder, float x )
{
	float h;
	if (x > 0.04){h = 0.08-x; }
	else {h=x;}
	glLoadIdentity();                 // Reset the model-view matrix
	glTranslatef(cylinder->pos[0] +(x*3),cylinder->pos[1]+(h*4.1),cylinder->pos[2]-(x*4));  // Move right and into the screen
   
	glMaterialfv(GL_FRONT, GL_AMBIENT_AND_DIFFUSE, cylinder->color);
   
	glRotatef(90-(x*7500),1,0,0);
	GLUquadricObj *quad= gluNewQuadric();
	gluCylinder(quad,cylinder->radius,cylinder->radius,cylinder->height,100,1);

	//top circle
	
	glLoadIdentity();                 // Reset the model-view matrix
	glTranslatef(cylinder->pos[0] +(x*3),cylinder->pos[1]+(h*4.1),cylinder->pos[2]-(x*4));
	
	glRotatef(270-(x*7500),1,0,0);
	
	gluDisk(quad, 0,cylinder->radius,100,1);

	//bottom circle need to fix
	
	glLoadIdentity();                 // Reset the model-view matrix
	glTranslatef(cylinder->pos[0] +(x*3),(cylinder->pos[1]-0.025)+(h*4.1),cylinder->pos[2]-(x*4));
	
	glRotatef(270-(x*7500),1,0,0);

	
	
	gluDisk(quad, 0,cylinder->radius,100,1);
      
	gluDeleteQuadric(quad);
}
void drawFloor(){
// Render a floor  
   glLoadIdentity();                 // Reset the model-view matrix
   glTranslatef(0.0f, 0.0f, -1.0f);  // Move right and into the screen
 
   GLfloat color1[] = { 1.0, 1.0, 0.5, 0.5 };
   glMaterialfv(GL_FRONT, GL_AMBIENT_AND_DIFFUSE, color1);

   glBegin(GL_POLYGON);  
      glVertex3f( 1.5f, -1.0f, -8.0f);
      glVertex3f(-1.5f, -1.0f, -8.0f);
      glVertex3f(-1.5f, -1.0f,  -2.0f);
      glVertex3f( 1.5f, -1.0f,  -2.0f);
      
   glEnd();  // End of drawing floor
}
void lightShadding(){
    // enable culling, depth-testing and lighting
    glEnable(GL_CULL_FACE);
    glEnable(GL_DEPTH_TEST);
    glEnable(GL_LIGHTING);
    // light source
    GLfloat lightPos[] = { 2, 3, 2, 1 };
    GLfloat lightColor[] = { 1, 1, 1, 1 };
    glLightfv(GL_LIGHT1, GL_POSITION, lightPos);
    glLightfv(GL_LIGHT1, GL_DIFFUSE, lightColor);
    glEnable(GL_LIGHT1); 
}
/* Initialize OpenGL Graphics */
void initGL() {
   glClearColor(0.0f, 0.0f, 0.0f, 1.0f); // Set background color to black and opaque
   glClearDepth(1.0f);                   // Set background depth to farthest
   glEnable(GL_DEPTH_TEST);   // Enable depth testing for z-culling
   glDepthFunc(GL_LEQUAL);    // Set the type of depth-test
   glShadeModel(GL_SMOOTH);   // Enable smooth shading
   glHint(GL_PERSPECTIVE_CORRECTION_HINT, GL_NICEST);  // Nice perspective corrections
}
 
/* Handler for window-repaint event. Called back when the window first appears and
   whenever the window needs to be re-painted. */
void display() {

	
	int i;

	for (i = 0; i < MAX_LAYER; i++){
		float x = 0;
		while (x <= 0.05){
			glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT); // Clear color and depth buffers
			glMatrixMode(GL_MODELVIEW);     // To operate on model-view matrix

			lightShadding();

			if (i > 0){drawCake(&layers[0],0.05);}
			if (i > 1){drawCake(&layers[1],0.05);}
			drawCake(&layers[i],x);
		
			drawFloor();
			drawCandle(&candle[0],0);

			glutSwapBuffers();  // Swap the front and back frame buffers (double buffering)

			x = x + 0.001;
			sleep(20);
	}}

	
		float x = 0;
		while (x <= 0.05){
			glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT); // Clear color and depth buffers
			glMatrixMode(GL_MODELVIEW);     // To operate on model-view matrix

			lightShadding();
			for (i = 0; i < MAX_LAYER; i++){ drawCake(&layers[i],0.05);}
		
			drawFloor();
			drawCandle(&candle[0],x);

			glutSwapBuffers();  // Swap the front and back frame buffers (double buffering)

			x = x + 0.001;
			sleep(50);
	}
}
 
/* Handler for window re-size event. Called back when the window first appears and
   whenever the window is re-sized with its new width and height */
void reshape(GLsizei width, GLsizei height) {  // GLsizei for non-negative integer
   // Compute aspect ratio of the new window
   if (height == 0) height = 1;                // To prevent divide by 0
   GLfloat aspect = (GLfloat)width / (GLfloat)height;
 
   // Set the viewport to cover the new window
   glViewport(0, 0, width, height);
 
   // Set the aspect ratio of the clipping volume to match the viewport
   glMatrixMode(GL_PROJECTION);  // To operate on the Projection matrix
   glLoadIdentity();             // Reset
   // Enable perspective projection with fovy, aspect, zNear and zFar
   gluPerspective(45.0f, aspect, 0.1f, 100.0f);
}
 
/* Main function: GLUT runs as a console application starting at main() */
int main(int argc, char** argv) {
   glutInit(&argc, argv);            // Initialize GLUT
   glutInitDisplayMode( GLUT_DOUBLE | GLUT_RGB | GLUT_DEPTH ); // Enable double buffered mode
   glutInitWindowSize(640, 480);   // Set the window's initial width & height
   glutInitWindowPosition(50, 50); // Position the window's initial top-left corner
   glutCreateWindow(title);          // Create window with the given title
   glutDisplayFunc(display);       // Register callback handler for window re-paint event
   glutReshapeFunc(reshape);       // Register callback handler for window re-size event
   initGL();                       // Our own OpenGL initialization
   glutMainLoop();                 // Enter the infinite event-processing loop
   return 0;
}

