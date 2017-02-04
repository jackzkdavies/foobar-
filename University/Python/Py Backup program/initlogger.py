# Isaac Carrington
import time
import logging
import logging.handlers

import init

loggingLocation = init.objectsDir()
loggingLocation = loggingLocation.replace("objects", "")



def init_logging():
    PROGRAM_NAME      ="myBackup"
    LOG_FILENAME      = PROGRAM_NAME + '.log'
    
    CONSOLE_LOG_LEVEL = logging.INFO  # Only show errors to the console
    FILE_LOG_LEVEL    = logging.WARNING   # but log info messages to the logfile
    
    logger = logging.getLogger(PROGRAM_NAME)
    logger.setLevel(logging.DEBUG)
    
    #====================================================================================
    # FILE-BASED LOG
    
    logging.basicConfig(filename=(loggingLocation + 'AchieveLogs.log'),level=logging.DEBUG)    
    # Create formatter and add it to the handlers
    formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    
    
    # LOGFILE HANDLER - SELECT ONE OF THE FOLLOWING TWO LINES
    
    fh = logging.FileHandler(LOG_FILENAME)                          # Continuous Single Log
    
    fh.setLevel(FILE_LOG_LEVEL)
    fh.setFormatter(formatter)
    logger.addHandler(fh)
    
    
    # Add timestamp
    logger.warning('\n---------\nLog started on %s.\n---------\n' % time.asctime())
    
    #=================================================================================
    # CONSOLE HANDLER - can have a different loglevel and format to the file-based log 
    ch = logging.StreamHandler()
    ch.setLevel(CONSOLE_LOG_LEVEL)
    formatter = logging.Formatter('%(message)s')     # simpler display format
    ch.setFormatter(formatter)
    logger.addHandler(ch)
    return logger
