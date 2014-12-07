package openCl;

import static org.jocl.CL.*;

import org.jocl.*;

/**
 * Concurrent Programming Assignment.
 */
public class OpenCL
{
    /**
     * The source code of the OpenCL program to execute
     */
    private static String programSource =
            "__kernel void "+
            "sampleKernel(__global const long *inputA,"+
            "             __global long *inputB,"+
            "			  __global long *output)"+	
            "{"+
            "   int gid = get_global_id(0);"+
            
            "	long inputValue = inputB[0];"+
            "	long workItemRange = inputB[1];"+
            
            "   if (output[0] != 0){return;}"+
            "	for (long f = inputA[gid]; f <= (inputA[gid]+workItemRange); f+=2)"+
            "		if(inputValue%f==0){"+
            "			output[0]=f;}"+
            "}";
        

    /**
     * The entry point
     * 
     * 
     */
    public static void main(String args[])
    {
    	// Set Global and Local work size (global must be smaller then sqr of input and local must equally divide gws)
        int gws = 10000;
        int lws = 100;
    	
    	// Create input- and output data
//        long input = 7907*7919 ;
        long input = 2147483647 * 4294967291L ;
        long inputSqurd = (long) Math.sqrt(input);


        long inputSqrtDivWorkload = inputSqurd/lws; 
        long workItemRange = inputSqrtDivWorkload/lws;
        
        
        long srcArrayA[] = new long[gws+1];
        long srcArrayB[] = new long[2];
        long dstArray[] = new long[1];

        
        //  srcArrayA 
        srcArrayA[0] = 3; 

        int index = 1;
        for (long i=workItemRange; i<inputSqurd; i+=workItemRange)
        { 		
        	//check starts on odd number
        	if (i % 2 == 0){i--;}
        	
            srcArrayA[index] = i; 
//            System.out.println(srcArrayA[index]);
            index++;
        }
        
        // srcArrayB
        srcArrayB[0] = input;
        srcArrayB[1] = workItemRange;


    	Pointer srcA = Pointer.to(srcArrayA);
    	Pointer srcB = Pointer.to(srcArrayB);
    	Pointer dst = Pointer.to(dstArray);

    	// The platform, device type and device number
    	// that will be used
    	final int platformIndex = 0;
    	final long deviceType = CL_DEVICE_TYPE_ALL;
    	final int deviceIndex = 0;
    	
      	// Enable exceptions and subsequently omit error checks in this sample
    	CL.setExceptionsEnabled(true);

    	// Obtain the number of platforms
    	int numPlatformsArray[] = new int[1];
    	clGetPlatformIDs(0, null, numPlatformsArray);
    	int numPlatforms = numPlatformsArray[0];

    	// Obtain a platform ID
    	cl_platform_id platforms[] = new cl_platform_id[numPlatforms];
    	clGetPlatformIDs(platforms.length, platforms, null);
    	cl_platform_id platform = platforms[platformIndex];

    	// Initialize the context properties
    	cl_context_properties contextProperties = new cl_context_properties();
    	contextProperties.addProperty(CL_CONTEXT_PLATFORM, platform);
   	 
    	// Obtain the number of devices for the platform
    	int numDevicesArray[] = new int[1];
    	clGetDeviceIDs(platform, deviceType, 0, null, numDevicesArray);
    	int numDevices = numDevicesArray[0];
   	 
    	// Obtain a device ID
    	cl_device_id devices[] = new cl_device_id[numDevices];
    	clGetDeviceIDs(platform, deviceType, numDevices, devices, null);
    	cl_device_id device = devices[deviceIndex];

    	// Create a context for the selected device
    	cl_context context = clCreateContext(
        	contextProperties, 1, new cl_device_id[]{device},
        	null, null, null);

        // Create a command-queue
        cl_command_queue commandQueue = 
            clCreateCommandQueue(context, devices[0], 0, null);

    	// Allocate the memory objects for the input- and output data
        cl_mem memObjects[] = new cl_mem[3];
        memObjects[0] = clCreateBuffer(context, 
        	CL_MEM_READ_ONLY | CL_MEM_COPY_HOST_PTR,
        	Sizeof.cl_long * gws, srcA, null); 
        memObjects[1] = clCreateBuffer(context, 
                CL_MEM_READ_ONLY | CL_MEM_COPY_HOST_PTR,
                Sizeof.cl_long * 2, srcB, null);
        memObjects[2] = clCreateBuffer(context, 
            CL_MEM_READ_ONLY | CL_MEM_COPY_HOST_PTR,
            Sizeof.cl_long * gws, dst, null);

        // Create the program from the source code
        cl_program program = clCreateProgramWithSource(context,
            1, new String[]{ programSource }, null, null);
        
        // Build the program
        clBuildProgram(program, 0, null, null, null, null);
        
        // Create the kernel
        cl_kernel kernel = clCreateKernel(program, "sampleKernel", null);
        
        // Set the arguments for the kernel
        clSetKernelArg(kernel, 0, 
            Sizeof.cl_mem, Pointer.to(memObjects[0]));
        clSetKernelArg(kernel, 1, 
            Sizeof.cl_mem, Pointer.to(memObjects[1]));
        clSetKernelArg(kernel, 2, 
            Sizeof.cl_mem, Pointer.to(memObjects[2]));

        // Set the work-item dimensions
        long global_work_size[] = new long[]{gws};
        long local_work_size[] = new long[]{lws}; 
        
        // Execute the kernel
        clEnqueueNDRangeKernel(commandQueue, kernel, 1, null,
            global_work_size, local_work_size, 0, null, null);
        
        // Read the output data
        clEnqueueReadBuffer(commandQueue, memObjects[2], CL_TRUE, 0,
        		gws * Sizeof.cl_long, dst, 0, null, null);
        
        // Release kernel, program, and memory objects
        clReleaseMemObject(memObjects[0]);
        clReleaseMemObject(memObjects[1]);
        clReleaseMemObject(memObjects[2]);
        clReleaseKernel(kernel);
        clReleaseProgram(program);
        clReleaseCommandQueue(commandQueue);
        clReleaseContext(context);
       
        System.out.println("The two primes of the given RSA " +input+ " are: ");
        System.out.println(dstArray[0] +" and "+ (input/dstArray[0]));


    }
}
