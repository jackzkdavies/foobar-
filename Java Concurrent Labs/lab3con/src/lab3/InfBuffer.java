package lab3;
import java.util.LinkedList;
import java.util.List;

import lab3.Producer;
import lab3.Buffer;
import lab3.Consumer;
import lab3.ReenBuffer;

public class InfBuffer {

	
	public static void main(String args[]) {
		Buffer buffer = new Buffer();
		

		 
		(new Thread(new Producer(buffer))).start();
		(new Thread(new Producer(buffer))).start();
		
		(new Thread(new Consumer(buffer))).start();
		(new Thread(new Consumer(buffer))).start();
	}
}

