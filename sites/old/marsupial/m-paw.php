<?php include("includes/banner.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/stats_collection.php"); ?>

	<td  width="83% "valign="top" align="left">

		<table align="right" cellspacing=0 cellpadding=5 bordercolor="#FF9900"
					bgcolor=white style='width:100%; mso-cellspacing:0cm; background:white; 
					border:outset #FF9900 1pt; mso-padding-alt:1.0pt 1.0pt 1.0pt 1.0pt'>
     		<tr>      	
			<td>
                 <span class="blackheading">Design Concept Archive</span><br>
                 <span class="bodytext"><br>
				 <?php include("includes/design_menu.php"); ?>

<p><b>M-PAW CONCEPT OVERVIEW<a name="M-Paw"></a></b><br>
			        </p>
<p><img src="/marsupial/images/M-Paw1.JPG" width="289" height="219"><br>
      
<br>
This document outlines the general arrangement and capabilities of a Remote Manipulator Arm (RMA) which could be used with the Starchaser Marsupial, giving the results of early concept sketches and design meetings. What you see here is an exploratory concept at this stage - the arm may/may not be eventually built, and if it is the configuration and control system  may change significantly from that stated here.
 
<br>  <br>
      <br>
      <b>Background</b><br>
      <br>
    
The Starchaser Marsupial requires a remotely operated manipulator arm that can be used to perform outside tasks, as well as collect geological samples.
    <br>
    <br><br>
    
    <b>Goals</b><br>
    <br>
    
It is highly desirable to achieve a level of speed and utility that allows realistic tasks to be performed without the human operators being severely constrained by the performance limits of the RMA unit.
    <br>
    <br>
    
The resultant device should be reliable and 'field friendly', being able to be maintained and repaired with a small inventory of spare parts. It should be suitable for operation mounted on a vehicle in the Australian outback environment.
    <br>
    <br>
    
The RMA must be sufficiently flexible to allow both delicate pick and place operations, as well as digging operations that require relatively large forces to be exerted on the tool tip.
    <br>
    <br><br>
    
    <b>Description</b><br>
    <br>
    
The arrangement given here represents a design the we feel can be reasonably achieved within the time and budget constraints of the project. 
    <br>
    <br>
    
    <i>RMA General arrangement</i><br>
    <br>
    
The Proposed RMA manipulator is intended to allow object to be grasped and manipulated within the machine's operating envelope. 
    <br>
    <br>
    The overall arrangement of the RMA manipular is similar to that of a Unimate® arm, consisting of three overlapping segments rather than the two commonly seen on Unimate units. <br>
    <br>
    
The RMA operates in a single plane, with a shoulder rotation that allows the gripper to access most points in the available volume when the unit is mounted on the corner of a vehicle.
    <br>
    <br>
    
The shoulder is shown as compound joint that allows the RMA to be swung out from side of the vehicle to facilitate reaching forward, we hope to make this a powered axis. 
    <br>
    <br>
    
It is expected that the RMA will be able to reach as far forward as the front bumper of the utility and up to 2.5m out from the side, although the unit's height above the ground will probably limit the ground level reach to 1.5 to 1.8 metres radius. Over 90 percent of the HOP utility bed should be accessible to the RMA.
    <br>
    <br>
    
The wrist comprises 3 separate movement axes, allowing the gripper to approach an object from most directions. The gripper removable from the tool plate, allowing alternate grippers to be attached. The tool plate is capable of continuous rotation.
    <br>
    <br>
    
The relative lengths of the arm segments represent an estimate. The actual lengths of the segments are subject to adjustment to fit within the operational and mechanical requirements. 
    <br>
    <br>
    
    <i>Mechanical and control description</i><br>
    <br>
    
The RMA is intended initially as a close proximity teleoperated device with capabilities to be extended to more automatic operation. Automation beyond basic control of the device and translation of the operator input into end effector movements is beyond the scope of this project.
    <br>
    <br>
    
    <i>Distributed control</i><br>
    <br>
    The control system for the RMS is intended to be entirely contained within the RMA unit and its control console with the external wiring to be as simple as possible. We have opted for a distributed control system using identical control units at each joint, serially bussed to the control console where the geometric computations are performed. 
    <br>
    <br>
    
    <i>Health state monitoring</i><br>
    <br>
    
The control system must be able to monitor its own status, detecting such things as component temperatures, output current, motor torque, speed and position. The control system must detect problems and warn the operator or shut down before they become catastrophic. 
    <br>
    <br>
    <i>Positioning feedback</i><br>
    <br>
    
It is desirable to be able to achieve an end effector resolution of approximately 1 mm over the working volume. The use of 12 bit (analog) sampling of joint position allows this to be approached. Given the mountings and environment, we feel that this is sufficient.
    <br>
    <br>
    
    <i>Force/Impedance control</i><br>
    <br>
    
In addition to position control, the RMA is also required to be able to measure and limit the force it applies during particular operations it performs. Force control is particularly important during the digging operation to ensure that the machine limits are handled in such a way that the operators desired result is still achieved. To be investigated.
    <br>
    <br>
    
    <i>Motor Power requirements</i><br>
    <br>
    The original concept sketches proposed using linear electric actuators, such as those made by Warner® or Linak®. These actuators provide a 'hydraulic like' mode of operation. When the desired operation of the RMA was considered, these units were found to be far too slow. The most suitable units were approximately 50 watts maximum. <br>
    <br>
    
The speed of operation is largely determined by the power available from the motors. Motors that can achieve continuous operation in the order of 100 watts and short term peak power of 500 watts are necessary to achieve the loaded movement speeds required. The gearing will be selected to achieve the desired output joint torques. 
    <br>
    <br>
    
    <i>Arm construction/Weight constraints</i><br>
    <br>
    
This type of arm design where the actuator motors are embedded in the arm mechanics require drives that are powerful, light and cheap. Most of the drives are mounted within the arm structure and must be lifted by the shoulder and other joints. The location of the heavy components must consider this.
    <br>
    <br>
    
The structure of the RMA arm must be light and stiff. We are considering using carbon and glass fibre over foam for a substantial amount of the structure. These materials are easy to work and can make very light and strong components. No detail design of the  arm structure has been completed at this time. It is expected that the arm segments will be hollow boxes with access plates and covers to reach the mechanicals inside. 
    <br>
    <br>
    
    <i>Wrist dexterity</i><br>
    <br>
    
The range of movement achieved by the wrist is important is permitting ease of use RMA. The current wrist design, the third configuration examined, permits relatively movements. In the above diagram, three movement axes are devoted to the wrist.
    <br>
    <br>
    Axis 5, the wrist rotation, will be able to rotate between 540 and 720 degrees, constrained by the power and data cabling on the drive system.
    <br>
    <br>
    Axis 6, the wrist flexure, will be either 90 or 180 degrees. All positions can be achieved by the combination of Axes 5 and 6. Additional flexure may make teleoperation operation easier. The 90 degree option allows a hard stop to included for bracing the wrist when digging.
    <br>
    <br>
    Axis 7, the tool plate, will be able to rotate continuously.
    <br>
    <br><br>
    
    <b>Specifications</b><br>
    <br>
    The specifications indicated here are the results of preliminary investigation. 
    We welcome your feedback regarding these.<br>
    <br>
    <i>Power supply</i><br>
    <br>
    24 Volts DC.<br>
    <br>
    
The expected the quiescent current draw with the arm parked to be approximately 250 milliamps. 
   
    Likely average operating current draw to be somewhat less than 7.5 amps (180 Watts) during typical movements. 
 
    Peak operating current during a digging operation likely to be as high as 75 for approximately 5 seconds.<br>
    <br>
    <i>Lifting load</i><br>
    <br>
    The lifting load of 10 Kg is required at a 1 metre reach. 
   
    The RMA design should aim to achieve this goal with a degree of reserve capacity.<br>
    <br>
    <i>Excavations</i><br>
    <br>
    In order to drive a scoop through loosened, compacted soil, a considerable force must be applied to the scoop. We have estimated this at 500N. Some experimentation is required to confirm this value.
    <br>
    <br>
    The RMA must be able to dig a 300 mm deep hole and remove scoopfuls of soil to receptacles, located on the tray or ground nearby.
    The movement between digging site and receptacles should be automated, relieving the operator of the movement commands for this element. We will investigate this.    </p>
<p><i>Movement speed</i><br>
      <br>
      The move speed of 250mm per second maximum has been chosen to give a typical 1 metre move time of about 5 seconds. Ideally, the move speed of the RMA should be such that large movements, such as digging site to tray are accomplished within 10 seconds. <br>
      <br>
      One of the limiting factors is actuator power. The actuators must be able to deliver enough power to lift the RMA and its load at the minimum required speed. The speeds are assumed at mid reach. Close in operation is permitted to be slower. Overall end effector speed to be limited by the control software, permitting safe operating speed with respect to collisions with human bystanders. <br>
      <br>
      <i>Movement limitations</i>
      <br>
      <br>
      The manipulator should be able to reach most locations on the HOP bed and lift objects and loads from the bed.
      The controller should impose movement limits that prevent the HOP arm from colliding with the structure of the HOP utility. <br>
      <br>
      <i>Safety</i>
      <br>
      <br>
      Operator and bystander safety must be designed into the RMA. Provision of prominent emergency stop buttons to be made near to the end of the arm. Additional stop buttons on the vehicle body and console unit. <br>
      

      
                    <br>
                  </p>
</span>
			</td>
             </tr>
        </table>


</td>
</tr>
</table>

<?php include("includes/footer.php"); ?>