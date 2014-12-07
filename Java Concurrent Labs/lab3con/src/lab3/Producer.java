package lab3;

class Producer implements Runnable {
	Buffer buffer;

	public Producer(Buffer b) {
		buffer = b;
	}

	public void run() {
		
		try {
			for (int i=0; i<10; i++){
				buffer.write(i);
				Thread.sleep(50);}
			} 
			catch (InterruptedException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		
		}
	}
}

