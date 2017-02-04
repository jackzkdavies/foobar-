package lab2;

import lab2.Simulate;

public class Counter {
	volatile int value = 0;

	Counter() {
		System.out.println("TOTAL: " + value);
	}

	synchronized boolean increment() {
  	  int temp = value;   //read[v]
  	  Simulate.HWinterrupt();
  	  value = temp + 1;       //write[v+1]
  	  System.out.println("TOTAL: " + value);
			return true;
	}

	synchronized boolean decrement() {
  	  int temp = value;   //read[v]
  	  Simulate.HWinterrupt();
			if (temp == 0) return false;
  	  Simulate.HWinterrupt();
  	  value = temp - 1;       //write[v+1]
  	  System.out.println("TOTAL: " + value);
			return true;
	}
}

class Simulate {
    public synchronized static void HWinterrupt() {
        if (Math.random() < 0.5)
           try{
           	Thread.sleep(200);
           } catch(InterruptedException ie) {};
    }
}

