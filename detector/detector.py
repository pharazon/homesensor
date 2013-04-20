import serial
import time

import MySQLdb
import sys
from datetime import datetime
from threading import Thread


def insert_to_db(date, value, sensorid=4):
    db = MySQLdb.connect(user="root", passwd="", db="Lampo")
    c = db.cursor()
    c.execute("""insert into Mittaukset (Aika, Anturi, Lampotila) values (%s, %s, %s)""",
	     (date.strftime("%Y-%m-%d %H:%M:%S"),sensorid, value*3600))
    c.close()
    db.close()

port = serial.Serial('/dev/ttyS0')
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

#file = open("log_detector.txt", "a", 6)
#error_file = open("error_detector.txt", "a", 6)

while True:
  level = port.getCTS()
  if (level == False and old_level == True):
#    print "trigger: FALLING "+str(count)
    count += 1
    trigger_period.append((time.time() - trigger_time))
    trigger_time = time.time()
  if (level == True and old_level == False):
#    pass
#    print "trigger: RISING "+str(count)
#    count += 1
    pulse_width.append((time.time() - trigger_time))
  old_level = level
  time.sleep(0.010)
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

    print("%.2f edges/second, min: %f avg: %f max: %f" % (edges, min(pulse_width),pulse_avg,max(pulse_width))) 
    #print trigger_period
    timestr = time.strftime("%Y-%m-%d-%H-%M-%S")
    #file.write("%s %f %f %f %f %f %f %f\n" % (timestr, edges, min(pulse_width),pulse_avg,max(pulse_width), min(trigger_period),trigger_avg,max(trigger_period)))

    t = Thread(target=insert_to_db, args=(time,edges))
    t.start()

    pulse_width = []
    trigger_period = []
    start = time.time()
    count = 0

  if (time.time() - loop_time) > 0.001:
    timestr = time.strftime("%Y-%m-%d-%H-%M-%S")
    #error_file.write("%s %f\n" % (timestr,  time.time() - loop_time ))
  loop_time = time.time()


