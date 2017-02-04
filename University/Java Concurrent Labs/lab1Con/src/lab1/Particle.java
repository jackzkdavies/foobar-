/*
 * Created on Mar 6, 2006
 *
 */
package lab1;

import java.awt.Graphics;
import java.util.Random;
import java.awt.Color;


class Particle extends Thread {
	protected int x;
	protected int y;
	protected final Random rng = new Random(this.hashCode());

	public Particle(int initialX, int initialY) { 
		x = initialX;
		y = initialY;
	}

	public void run() {
		while (true) {
			try {
				while (true) {
					move();
					sleep(100); // arbitrary wait time
				}
			} catch (InterruptedException ie) { 
				return; 
			}
		}
	}

	public synchronized void move() {
		x += (rng.nextInt() % 10);
		y += (rng.nextInt() % 10);
	}

	public void draw(Graphics g) {
		int lx, ly;
		
		Color c = new Color(getRandomNumberFrom(1, 255),getRandomNumberFrom(1, 255),getRandomNumberFrom(1, 255));
		g.setColor(c);
		synchronized (this) { lx = x; ly = y;}
		g.drawRect(lx, ly, getRandomNumberFrom(1, 20), getRandomNumberFrom(1, 20));
	}
	public static int getRandomNumberFrom(int min, int max) {
        Random foo = new Random();
        int randomNumber = foo.nextInt((max + 1) - min) + min;

        return randomNumber;

    }
}

