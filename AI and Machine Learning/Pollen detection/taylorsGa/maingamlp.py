'''
Created on Sep 12, 2014

@author: Taylor
'''


import numpy as np
import pylab as pl

import ga


pl.ion()
pl.show()

plotfig = pl.figure()

ga = ga.ga(9,'fF.mlpga',301,10,1,'un',4,False)

ga.runGA(plotfig)

pl.pause(0)
pl.show()