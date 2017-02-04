package lab3;

import java.util.LinkedList;
import java.util.NoSuchElementException;
import java.util.concurrent.locks.ReentrantLock;

public class ReenBuffer {
//	LinkedList<Integer> queue;
	LinkedList<Integer> queue = new java.util.LinkedList<Integer>();
	private final ReentrantLock lock = new ReentrantLock();
	
	public void write(int i) {
		lock.lock();
		try{ 
			queue.add(i);}
		finally {
			lock.unlock();}
//		System.out.println(queue.size());
	}

	public int read() {
		try {
			System.out.println("read");
			return queue.removeFirst();
			
		} catch(NoSuchElementException e) {
			// the buffer is empty!?
			return -1;
		}
	}
}