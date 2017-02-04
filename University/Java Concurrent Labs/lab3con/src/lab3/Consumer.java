package lab3;

class Consumer implements Runnable {
	Buffer buffer;

	public Consumer(Buffer b) {
		buffer = b;
	}

	public void run() {
			try {
				Thread.sleep(500);
				while (buffer.queue.size() != 0){
				Thread.sleep(50);
				System.out.println(buffer.read());}
			} catch (InterruptedException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			
		}
	
}

