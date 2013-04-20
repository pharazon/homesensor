import serial
import time

import MySQLdb
import sys
from datetime import datetime
from threading import Thread
from configobj import ConfigObj

def insert_to_db(dbcfg, date, value, sensorid=4):
    db = MySQLdb.connect(user=dbcfg['username'], passwd=dbcfg['password'], db=dbcfg['name'])
    c = db.cursor()
    c.execute("""INSERT INTO Mittaukset (Aika, Anturi, Lampotila) VALUES (%s, %s, %s)""",
	     (date.strftime("%Y-%m-%d %H:%M:%S"), sensorid, value*3600))
    c.close()
    db.close()

cfg = ConfigObj('detector.ini')
port = serial.Serial('/dev/' + cfg['serial']['tty'])
port.setRTS(True)
port.setDTR(False)
meas_time = 30.0

level = port.getCTS()
old_level = level
count = 0
start = time.time()
loop_time = time.time()
trigger_limit = 0.1
trigger_time = time.time()
trigger_period = []
pulse_width = []

if (cfg['log']['file'] == True):
  file = open("log_detector.txt", "a", 6)
if (cfg['log']['error'] == True):
  error_file = open("error_detector.txt", "a", 6)

while True:
  level = port.getCTS()
  if (level == False and old_level == True):
    if (cfg['log']['debug'] == True):
      print "trigger: FALLING " + str(count)
    count += 1
    trigger_period.append((time.time() - trigger_time))
    trigger_time = time.time()
  if (level == True and old_level == False):
    if (cfg['log']['debug'] == True):
      # pass
      if (cfg['log']['verbose'] == True):
        print "trigger: RISING " + str(count)
        count += 1
    pulse_width.append((time.time() - trigger_time))
  old_level = level
  time.sleep(float(cfg['measurement']['sleep']))
  elapsed = (time.time() - start)
  if elapsed > meas_time:
    if count > 0:
      pulse_avg = sum(pulse_width)/len(pulse_width)
      trigger_avg = sum(trigger_period)/len(trigger_period)
      edges = count/meas_time
    else:
      pulse_avg = 0
      pulse_width = [0]
      trigger_avg = 0
      trigger_period = [0]
      edges = 0

    if (cfg['log']['verbose'] == True):
      print("%.2f edges/second, min: %f avg: %f max: %f" % (edges, min(pulse_width),pulse_avg,max(pulse_width))) 
    if (cfg['log']['debug'] == True):
      print trigger_period
    timestr = time.strftime("%Y-%m-%d-%H-%M-%S")
    if (cfg['log']['file'] == True):
      file.write("%s %f %f %f %f %f %f %f\n" % (timestr, edges, min(pulse_width),pulse_avg,max(pulse_width), min(trigger_period),trigger_avg,max(trigger_period)))

    t = Thread(target=insert_to_db, args=(cfg['db'], time, edges))
    t.start()

    pulse_width = []
    trigger_period = []
    start = time.time()
    count = 0

  if (time.time() - loop_time) > 0.001:
    timestr = time.strftime("%Y-%m-%d-%H-%M-%S")
    if (cfg['log']['error'] == True):
      error_file.write("%s %f\n" % (timestr,  time.time() - loop_time ))
  loop_time = time.time()

#Finalize
if (cfg['log']['detector'] == True):
  close(file)
if (cfg['log']['error'] == True):
  close(error_file)

