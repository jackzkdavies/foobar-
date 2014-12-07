package lab2;

public class Bakery {
	public int nThreads;
	
	private volatile int[] ticket;
	private volatile boolean[] choosing;
	
	Bakery(int nTh) {
		nThreads = nTh;
		ticket = new int[nThreads];
		choosing = new boolean[nThreads];
		
		for (int i = 0; i < nThreads; i++) {
			System.out.println(i + ticket[1]);
			ticket[i] = 0;
			choosing[i] = false;
		}
	}
	
	private int getNextNumber() {
		int largest = 0;
		for (int i = 0; i < nThreads; i++) {
			if (ticket[i] > largest)
				largest = ticket[i];
		}
		return largest + 1;
	}
	
	private boolean isFavouredThread(int i, int j) {
		if ((ticket[i] == 0) || (ticket[i] > ticket[j]))
			return false;
		else {
			if (ticket[i] < ticket[j])
				return true;
			else
				return (i < j);
		}
	}

	void wantToEnterCS(int threadID) {
		choosing[threadID] = true;
		ticket[threadID] = getNextNumber();
		choosing[threadID] = false;
		
		for (int otherThread = 0; otherThread < nThreads; otherThread++) {
			while(choosing[otherThread]) {
				// busy-wait
			}
			// Break any ties between threads
			while (isFavouredThread(otherThread,threadID)) {
				// busy-wait
			}
		}
	}

	void exitCS(int threadID) {
		// Leave critical section
		ticket[threadID] = 0;
	}
}

