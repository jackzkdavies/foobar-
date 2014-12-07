/*
 * Created on Mar 6, 2006
 * Updated 2014
 *
 */
package lab1;

import java.awt.Canvas;
import java.awt.Dimension;
import java.awt.Graphics;
import java.awt.event.KeyEvent;
import java.awt.event.KeyListener;
import java.util.ArrayList;

class ParticleCanvas extends Canvas implements Runnable, KeyListener {

	private ArrayList<Particle> particles = new ArrayList<Particle>(); 

	public ParticleCanvas(int size) {
		setSize(new Dimension(size, size));
		addKeyListener(this);
	}

	public void run() {
		try {
			while (true) {
				repaint();
				Thread.sleep(100); // arbitrary wait
			}
		} catch (InterruptedException ie) { 
			return; 
		}
	}

	// Called by the applet
	synchronized void setParticles(ArrayList<Particle> ps) {
		if (ps.isEmpty()) 
		throw new IllegalArgumentException("Cannot set null");

		particles = ps; 
	}

	protected synchronized ArrayList<Particle> getParticles() { 
		return particles; 
	}

	@Override
	public void paint(Graphics g) {
		ArrayList<Particle> ps = getParticles();

		for (int i = 0; i < ps.size(); i++) ps.get(i).draw(g);
	}

	public void keyTyped(KeyEvent ke) {
		// This reads a character
		char ch = ke.getKeyChar();
		System.out.println(ch);

		// Your job is to make these keys do something!

		switch (ch) {
			case 'r':  // reset particles 
				break;
			case 'n': 
					for (int i =0;i<5000;i++){
					Particle partTemp = new Particle(200, 200);
					partTemp.setName("Particle Thread " + (particles.size() +1));
					partTemp.start();
					particles.add(partTemp); }
				break;
			case 'd':  
					particles.remove(particles.size()-1);
				break;
			case 'q': particles.clear();
				break;
			default:
				break;
		}
	}

	public void keyPressed(KeyEvent ke) {
		// Empty method
	}

	public void keyReleased(KeyEvent ke) {
		// Empty method
	}
}

