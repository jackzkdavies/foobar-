package lab2;

import lab2.Bakery;
import lab2.Counter;
import lab2.Turnstile;

/* Notes:
 * No modifications need be made to the bakery class.  Instead, the turnstile
 * class and counter classes are adjusted to allow for "exit" turnstiles as well
 * as entrance turnstiles.  This solution incorporates a system to prevent the
 * number of people in the gardens from being negative, so the sleep call in
 * this class can be commented out.  Study the code in the Counter and Turnstile
 * classes to see how how this works.
 * */

public class Gardens {
	 static Turnstile west;
	 static Turnstile east;
	 static Turnstile westExit;
	 static Turnstile eastExit;
	 static Counter people;
	 static Bakery bakery;
	 
	public static void main(String[] args) {
		people = new Counter();
		
	 	
	 	west = new Turnstile(0, true, people);
	 	east = new Turnstile(1, true, people);

	 	westExit = new Turnstile(2, false, people);
	 	eastExit = new Turnstile(3, false, people);
	 	
	 	west.start();
	 	east.start();

		/* 
		try {
			Thread.sleep(5000);
		} catch (InterruptedException e) {
		}
		*/

		westExit.start();
		eastExit.start();
	}
}

