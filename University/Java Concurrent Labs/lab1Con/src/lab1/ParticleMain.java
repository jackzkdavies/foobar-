/*
 * Created on Mar 6, 2006
 *
 */
package lab1;

import java.util.ArrayList;

import javax.swing.*;

public class ParticleMain { 

	protected final ParticleCanvas canvas = new ParticleCanvas(400);
	protected ArrayList<Particle> particles = new ArrayList<Particle>(); // null when not running
	protected Thread canvasThread;

	private void createAndShowGUI() {
		JFrame frame = new JFrame("Particles");
		frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		frame.add(canvas);
		frame.pack();
		frame.setVisible(true);
	}

	public static void main(String[] args) {
		ParticleMain m = new ParticleMain();
		m.createAndShowGUI();
		m.start();
	}

	public synchronized void start() {
		int n = 10; 

		if (particles.isEmpty()) { // bypass if already started
//			particles = new Particle[n];
			for (int i = 0; i < n; i++) {
				Particle partTemp = new Particle(200, 200);
				partTemp.setName("Particle Thread " + i);
				partTemp.start();
				particles.add(partTemp);
//				particles[i] = new Particle(200, 200);
//				particles[i].setName("Particle Thread " + i);
//				particles[i].start();
			}
			canvas.setParticles(particles);
			canvasThread = new Thread(canvas);
			canvasThread.setName("Canvas Thread");
			canvasThread.start();
		}
	}

	public synchronized void stop() {
		if (particles != null) { // bypass if already stopped
			for (int i = 0; i < particles.size(); i++) particles.get(i).interrupt();
			particles = null;
			canvasThread.interrupt();
			canvasThread = null;
		}
	}
}

