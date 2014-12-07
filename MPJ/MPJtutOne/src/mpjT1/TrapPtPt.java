package mpjT1;

import mpi.*;

public class TrapPtPt { 
	
	static double f(double x) {
		return Math.sin(x);
	}
	
	static double Trap(double left_endpt, double right_endpt, int trap_count, double base_length) {
		double estimate, x;
		
		estimate = (f(left_endpt) + f(right_endpt))/2.0;
		
		for (int i=1; i<=trap_count-1; i++) {
			x = left_endpt + i*base_length;
			estimate += f(x);
		}
		estimate *= base_length;
		
		return estimate;
	}
	
	public static void main(String[] args) throws Exception {
		int n = 40960000, local_n;
		double a = 0.0, b = 999999999999999.0, h, local_a, local_b;
		double total_int=0.0;
		double local_int[] = new double[1];
		int source;
		
		double start = System.currentTimeMillis();
		MPI.Init(args); 
		int rank = MPI.COMM_WORLD.Rank();
		int size = MPI.COMM_WORLD.Size();
	
		h = (b-a)/n;
		local_n = n/size;
	
		local_a = a + rank*local_n*h;
		local_b = local_a + local_n*h;
		local_int[0] = Trap(local_a, local_b, local_n, h);
		
		if (rank != 0) {
			MPI.COMM_WORLD.Send(local_int,0,1,MPI.DOUBLE,0,99);
			
		} else {
			total_int = local_int[0];
			for (source = 1; source < size; source++) {
				MPI.COMM_WORLD.Recv(local_int,0,1,MPI.DOUBLE,source,99);
				total_int += local_int[0];
			}
		}
		
		if (rank == 0) {
			System.out.println("With n = " + n + " trapezoids, estimate of");
			System.out.println("integral from " + a + " to " + b + " is " + total_int);
		}
		
		MPI.Finalize();
		double end = System.currentTimeMillis();
		System.out.println("run time =" + (end-start));
		
		
	}
}
