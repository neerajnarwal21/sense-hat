from sense_emu import SenseHat
from enum import Enum
import time
import requests

class SetupMode(Enum):
    NORMAL = 1
    SETUP = 2
    
class ModeSelect(Enum):
    NONE = 0
    TEMP = 1
    HUMIDITY = 2

class CollisionState(Enum):
    NORMAL = "Normal"
    WINDY = "Windy"
    COLLISION = "Collision"

setupMode = SetupMode.NORMAL
modeSelect = ModeSelect.NONE 
collisionState = CollisionState.NORMAL

s = SenseHat()
temperature = 0.0
humidity = 0.0
tempThresh = 25.0
humiThresh = 45.0

acc = s.get_accelerometer_raw()
xOld = round(acc['x'],2)
yOld = round(acc['y'],2)
zOld = round(acc['z'],2)

lastRecordTime = time.time() - 60
setupTimeoutTime = time.time()

w = (255,255,255)
b = (0,0,0)
collLogo = [
w, b, b, b, b, b, b, w,
b, w, b, b, b, b, w, b,
b, b, w, b, b, w, b, b,
b, b, b, w, w, b, b, b,
b, b, b, w, w, b, b, b,
b, b, w, b, b, w, b, b,
b, w, b, b, b, b, w, b,
w, b, b, b, b, b, b, w,
]

while True:
    if setupMode == SetupMode.NORMAL and collisionState != CollisionState.COLLISION:
        currentTime = time.time()
        if currentTime - lastRecordTime >= 60:
            lastRecordTime = currentTime
            temperature = round(s.get_temperature(),2)
            humidity = round(s.get_humidity(),2)
            acceleration = s.get_accelerometer_raw()
            x = round(acceleration['x'],2)
            y = round(acceleration['y'],2)
            z = round(acceleration['z'],2)
            print("X, Y and Z: ", x, y, z)
            
            # Comparing changes
            change_x = abs(x - xOld)
            change_y = abs(y - yOld)
            change_z = abs(z - zOld)
            
            # Saving values
            xOld = x
            yOld = y
            zOld = z
            
            # Condition 1: When change is greater than 0.3
            if change_x > 0.3 or change_y > 0.3 or change_z > 0.3:
                print("Change greater than 0.3")
                s.show_message("Collision detected")
                s.set_pixels(collLogo)
                collisionState = CollisionState.COLLISION
            
            # Condition 2: When change is between 0.1 and 0.3
            elif (0.1 < change_x < 0.3) or (0.1 < change_y < 0.3) or (0.1 < change_z < 0.3):
                print("All change between 0.1 and 0.3")
                collisionState = CollisionState.WINDY
                
            # Condition 3: When change is less than 0.1
            elif change_x < 0.1 or change_y < 0.1 or change_z < 0.1:
                print("All change less than 0.1")
                collisionState = CollisionState.NORMAL
            
            # Get current time
            deviceTime = time.strftime("%Y-%m-%d %H:%M:%S")
            
            server = 'http://iotserver.com/recordtemp.php'
            payload = {
            't' : temperature, 
            'tt' : tempThresh, 
            'h': humidity, 
            'ht': humiThresh, 
            'dt': deviceTime, 
            'w': collisionState.value
            }
            r = requests.get(server, params = payload)
            if r.status_code == 200 and r.text.strip() == '1':
                # Resetting state to normal
                if collisionState != CollisionState.COLLISION:
                    collisionState = CollisionState.NORMAL
            else:
                s.show_message("No response from server")
    
    for event in s.stick.get_events():
        if event.action == "pressed":
            # Update the setupTimeoutTime whenever a button is pressed
            setupTimeoutTime = time.time()
            
            if event.direction == "middle":
                if collisionState == CollisionState.COLLISION:
                    collisionState = CollisionState.NORMAL
                    s.clear()
                elif setupMode == SetupMode.NORMAL:
                    print("In setup")
                    setupMode = SetupMode.SETUP
                    s.show_message("S")
                elif setupMode == SetupMode.SETUP:
                    print("In Normal")
                    setupMode = SetupMode.NORMAL
                    modeSelect = ModeSelect.NONE
                    s.show_message("N")
            elif event.direction == "left" and setupMode == SetupMode.SETUP:
                modeSelect = ModeSelect.TEMP
                s.show_message("T")
            elif event.direction == "right" and setupMode == SetupMode.SETUP:
                modeSelect = ModeSelect.HUMIDITY
                s.show_message("H")
            elif event.direction == "up":
                if modeSelect == ModeSelect.TEMP:
                    tempThresh += 1
                    s.show_message(f"T: {tempThresh}")
                elif modeSelect == ModeSelect.HUMIDITY:
                    humiThresh += 1
                    s.show_message(f"H: {humiThresh}")
            elif event.direction == "down":
                if modeSelect == ModeSelect.TEMP:
                    tempThresh -= 1
                    s.show_message(f"T: {tempThresh}")
                elif modeSelect == ModeSelect.HUMIDITY:
                    humiThresh -= 1
                    s.show_message(f"H: {humiThresh}")
    
    # Check if no button is pressed for 10 seconds
    if time.time() - setupTimeoutTime >= 10 and setupMode == SetupMode.SETUP:
        setupMode = SetupMode.NORMAL
        modeSelect = ModeSelect.NONE