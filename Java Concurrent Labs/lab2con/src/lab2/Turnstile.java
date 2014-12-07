package lab2;

import lab2.Bakery;
import lab2.Counter;

public class Turnstile extends Thread{
	
	int id;
	Counter people;
	Bakery bakery;
	boolean entrance;
	
	Turnstile(int id, boolean entr, Counter counter){
		this.id = id;
		this.people = counter;
		this.entrance = entr;
	}
	
	public synchronized void run(){
		try{
			int i = 1;
			while (i <= 10) {
				Thread.sleep(500);
			
				System.out.println(i + " " + getName());

				// where bakery used to be
				
				// this executes inside the critical section
				boolean s = false;
				if (entrance) s = people.increment(); else s = people.decrement();
				if (s) i++;
				// now leave the critical section
				//bakery.exitCS(id);
			}
		} catch (InterruptedException ie) {};
	}
}

