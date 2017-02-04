#if defined(_WIN32) || defined(_WIN64)
#include <windows.h>
#endif
#include <stdio.h>
#include <stdarg.h>
#include <string.h>
#include <math.h>
#include <GL/glut.h> 
#include <stdbool.h>
#include <time.h>


#define sqr(x) (x)*(x)
GLfloat batY = 0;
// utility functions

GLenum errorCheck ()
{
    GLenum code = glGetError ();
    if (code != GL_NO_ERROR)
        fprintf(stderr, "OpenGL error %d: %s\n", code, gluErrorString (code) );
    return code;
}

void createCenterWindow(const int sizeX, const int sizeY, const char* title)
{
    glutInitWindowPosition(
        (glutGet(GLUT_SCREEN_WIDTH) - sizeX) / 2,
        (glutGet(GLUT_SCREEN_HEIGHT) - sizeY) / 2
    );
    glutInitWindowSize (sizeX, sizeY);
    glutCreateWindow (title);
}

// executes f and measures time taken in seconds; multiple repeats increase accuracy
double profile(void (*f)(), int aveRepeats, int minRepeats)
{
    int i,j;
    SYSTEMTIME start, stop;
    double minTime = 60; // no speed test should take a minute

    for ( i = 0; i < minRepeats; i++ )
    {
        GetSystemTime(&start);
        for ( j = 0; j < aveRepeats; j++ )
            f();
        GetSystemTime(&stop);
        double diff = (stop.wSecond - start.wSecond) + (stop.wMilliseconds - start.wMilliseconds) * 0.000001;
        if ( diff < minTime )
            minTime = diff;
    }
    float temp = minTime / aveRepeats;
    printf ("%f", temp);
    return (minTime / aveRepeats)*1000;
}

// return value in the interval [current-delta, current+delta] closest to target
GLfloat converge(GLfloat current, GLfloat target, GLfloat delta)
{
    if (current + delta < target)
        return current + delta;
    if (current - delta > target)
        return current - delta;
    return target;
}

// ball data type - can be deformed
struct ball
{
    GLfloat pos[3]; // xy position
    GLfloat speed[3]; // xy speed
    GLfloat radius;
    GLfloat compression[3]; // xyz compression while bouncing - for calculation force
    GLfloat color[4];
};
typedef struct ball BALL;

// bat for striking the ball - just a flat panel
struct bat
{
    GLfloat pos[3]; // xy position (center)
    GLfloat size[3]; // xyz dimensions of the parallelepiped
    GLfloat color[4];
};
typedef struct bat BAT;

#define speedX 2
#define speedY 1.5

BALL ball = { {0,0,0}, {speedX, speedY}, 0.1, {1,1,1}, {1,0,0,1} };
BAT bats[2] = {
    { {-0.5,0,0}, {0.05, 0.3, 0.2}, {0,1,0,1} },
    { {0.5,0,0}, {0.05, 0.3, 0.2}, {0,0,1,1} },
};

// scale in given dimension based on compression (constant volume)
GLfloat getScale(BALL *ball, int dim)
{
    return ball->compression[dim] / sqrt( ball->compression[(dim+1) % 3] * ball->compression[(dim+2) % 3] );
}

// animation speed - initialized properly after system speed test
GLfloat speed = 0;

void drawBall(BALL *ball)
{
	glPushMatrix();
	
	GLfloat aspect = (GLfloat)600 / (GLfloat)800;

    // set color
    glMaterialfv(GL_FRONT, GL_AMBIENT_AND_DIFFUSE, ball->color);

    // adjust for position and scaling - TODO
	glTranslatef(ball->pos[0],0,ball->pos[1]);
	glScalef(ball->compression[0],ball->compression[1],ball->compression[2]);
    glutSolidSphere(ball->radius, 20, 20);

	glPopMatrix();
	
}

void drawBat(BAT *bat)
{
	
	glPushMatrix();

    // set color
    glMaterialfv(GL_FRONT, GL_AMBIENT_AND_DIFFUSE, bat->color);

    // adjust for position and scaling - TODO
	
	glTranslatef(bat->pos[0],bat->pos[1],bat->pos[2]);
	glScalef(bat->size[0],bat->size[1],bat->size[2]);
    glutSolidCube(1);

	
	glPopMatrix();
	
}

// draw all objects (cubes & plane)
void draw()
{
    int i;
    
    // clear the screen
    glClear( GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT );
    // draw the ball
    drawBall(&ball);
    // draw the two bats
    for (i = 0; i < 2; i++)
        drawBat(&bats[i]);
    // draw the scene
    glFlush();
    // change to new buffer (double buffering)
    glutSwapBuffers();
    // debug
    errorCheck();
}

bool upPressed = false;
bool downPressed = false;

void specialFunc(int key, int x, int y)
{
    switch(key)
    {
        case GLUT_KEY_UP: upPressed = true; break;
        case GLUT_KEY_DOWN: downPressed = true; break;
    }
}

void specialUpFunc(int key, int x, int y)
{
    switch(key)
    {
        case GLUT_KEY_UP: upPressed = false; break;
        case GLUT_KEY_DOWN: downPressed = false; break;
    }
}

void moveBall()
{
   ball.pos[0] = converge(ball.pos[0], ball.pos[0]+ball.speed[0], speed); 
   ball.pos[1] = converge(ball.pos[1], ball.pos[1]+ball.speed[1], speed); 
}

// reset ball after it has been lost
void resetBall()
{
    ball.pos[0] = 0;
    ball.pos[1] = 0;
}

void moveBats()
{
    if (upPressed && bats[1].pos[2] >= -(1.5)){
	bats[1].pos[2] = (bats[1].pos[2] - (speed*2)) ;
		
	}
	if (downPressed && bats[1].pos[2] <= (1.1)){
		bats[1].pos[2] = (bats[1].pos[2] + (speed*2));
	}
    bats[0].pos[2] = converge(bats[0].pos[2],ball.pos[1],(speed));
}


/**************************** ball bouncing - tricky **************************/

// updates to speed and compression for an elastic ball bouncing on a hard surface
// speed = movement speed of ball towards surface, relative to radius of one
// compression = reduced radius, relative to radius of one
void bounce(double hardness, GLfloat *speed, GLfloat *compression)
{
    double H = hardness, S = (*speed), C = (*compression);
    double avgC = (C - S/4); // new compression is roughly C - S/2
    double force = (1-avgC) / avgC * H; // physics simulation of decompression force
    *speed -= force; // acceleration is proportional to force
    *compression -= (*speed + S) / 4; // diameter changes by average speed
}

void bounceBall()
{
    double hardness = 10 * sqr(speed / ball.radius); // should be larger than (ball speed / radius) ^ 2
    double relFactor = speed / ball.radius;
    
    GLfloat bounceLeft = bats[0].pos[0] + bats[0].size[0] / 2;
    GLfloat bounceRight = bats[1].pos[0] - bats[1].size[0] / 2;
    GLfloat bounceUp = 1;
    GLfloat bounceDown = -1;

    // bounce on bats
    if ( ball.pos[0] + ball.radius > bounceRight && ball.pos[0] < bounceRight )
    {
        // make sure player bat is in place
        bool inPlace = fabs(ball.pos[1] - bats[1].pos[2]) < ball.radius + bats[1].size[1] / 2;
        if ( inPlace || ball.compression[0] != 1 ) // don't interrupt a bounce-in-progress
        {
            GLfloat relSpeed = ball.speed[0] * relFactor;
            bounce(hardness, &relSpeed, &ball.compression[0]);
            ball.speed[0] = relSpeed / relFactor;
            // ball touches bat during bounce
            ball.pos[0] = bounceRight - ball.radius * ball.compression[0];
        }
    }
    else if ( ball.pos[0] - ball.radius < bounceLeft )
    {
        GLfloat relSpeed = -ball.speed[0] * relFactor;
        bounce(hardness, &relSpeed, &ball.compression[0]);
        ball.speed[0] = -relSpeed / relFactor;
        // ball touches bat during bounce
        ball.pos[0] = bounceLeft + ball.radius * ball.compression[0];
    }
    else
    {
        // reset to avoid errors
        ball.compression[0] = 1;
        ball.speed[0] = ball.speed[0] < 0 ? -speedX : speedX;        
    }

    // invisible up/down boundaries
    if ( ball.pos[1] + ball.radius > bounceUp )
    {
        GLfloat relSpeed = ball.speed[1] * relFactor;
        bounce(hardness, &relSpeed, &ball.compression[1]);
        ball.speed[1] = relSpeed / relFactor;
        // ball touches bat during bounce
        ball.pos[1] = bounceUp - ball.radius * ball.compression[1];
    }
    else if ( ball.pos[1] - ball.radius < bounceDown )
    {
        GLfloat relSpeed = -ball.speed[1] * relFactor;
        bounce(hardness, &relSpeed, &ball.compression[1]);
        ball.speed[1] = -relSpeed / relFactor;
        // ball touches bat during bounce
        ball.pos[1] = bounceDown + ball.radius * ball.compression[1];
    }
    else
    {
        // reset to avoid errors
        ball.compression[1] = 1;
        ball.speed[1] = ball.speed[1] < 0 ? -speedY : speedY;
    }
}

/**************************** ball bouncing - done ****************************/

void update()
{
    moveBall();
    moveBats();
    bounceBall();

    if ( ball.pos[0] > 2 )
        resetBall();

    // redraw
    glutPostRedisplay();
}

int main (int argc, char** argv)
{
    // create window
	int height = 600;
	int width = 600;
    glutInit(&argc, argv);
    glutInitDisplayMode( GLUT_DOUBLE | GLUT_RGB | GLUT_DEPTH );
    createCenterWindow(height, width, "Pong 2.5D");
    
    // version info - only accessible after window has been created
    printf("OpenGL version: %s\n", glGetString(GL_VERSION));

    // enable culling, depth-testing and lighting
    glEnable(GL_CULL_FACE);
    glEnable(GL_DEPTH_TEST);
    glEnable(GL_LIGHTING);

    // set the view perspective- TODO
	if (height == 0) height = 1;                // To prevent divide by 0
	GLfloat aspect = (GLfloat)width / (GLfloat)height;

	glMatrixMode (GL_PROJECTION);
    gluPerspective(45.0f, aspect, 0.5f, 100.0f);
	glMatrixMode(GL_MODELVIEW);
    gluLookAt( 0,3,1, 0,0,0, 0,1,0 );

    // light source
    GLfloat lightPos[] = { 0, 0, 30, 1 };
    GLfloat lightColor[] = { 1, 1, 1, 1 };
    glLightfv(GL_LIGHT1, GL_POSITION, lightPos);
    glLightfv(GL_LIGHT1, GL_DIFFUSE, lightColor);
    glEnable(GL_LIGHT1);
    
    // animation speed based on system speed
    speed = profile(&draw, 20, 3);
    printf("speed = %f\n", speed);

    // setup for animation
    glutDisplayFunc(draw);
    glutIdleFunc(update);
    glutSpecialFunc(&specialFunc);
    glutSpecialUpFunc(&specialUpFunc);
    glutMainLoop( );
    
    return 0;
}
