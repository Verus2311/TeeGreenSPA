<?php
require_once('../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `appointments` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
    $qry2 = $conn->query("SELECT * FROM `patient_meta` where patient_id = '{$patient_id}' ");
    foreach($qry2->fetch_all(MYSQLI_ASSOC) as $row){
        $patient[$row['meta_field']] = $row['meta_value'];
    }
  }
?>
<style>
#uni_modal .modal-content>.modal-footer{
    display:none;
}
#uni_modal .modal-body{
    padding-bottom:0 !important;
}
</style>
<div class="container-fluid">
    <p><b>Appointment Schedule:</b> <?php echo date("F d, Y",strtotime($date_sched))  ?></p>
    <p><b>Patient Name:</b> <?php echo $patient['name'] ?></p>
    <p><b>Gender:</b> <?php echo ucwords($patient['gender']) ?></p>
    <p><b>Date of Birth:</b> <?php echo date("F d, Y",strtotime($patient['dob'])) ?></p>
    <p><b>Contact #:</b> <?php echo $patient['contact'] ?></p>
    <p><b>Age:</b> <?php echo isset($patient['Age']) ? $patient['Age'] : 'N/A' ?></p>
    <p><b>Address:</b> <?php echo $patient['address'] ?></p>
    <p><b>Ailment:</b> <?php echo $ailment ?></p>
    <p><b>Services and Therapists:</b></p>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Therapist</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $service_details = array(
                    "Triple Bogey Strokes" => "Triple Bogey Strokes (1hr & 15 mins ₱599.00)",
                    "Hole-in-One Strokes" => "Hole-in-One Strokes (50 mins ₱399.00)",
                    "Par Strokes" => "Par Strokes (50 mins ₱350.00)",
                    "Eagle Strokes" => "Eagle Strokes (50 mins ₱599.00)",
                    "Condor Stroke" => "Condor Stroke (2 hours ₱999.00)",
                    "Body Scrub" => "Body Scrub (30mins ₱350.00)",
                    "Body Scrub w/ full body massage" => "Body Scrub w/ full body massage (₱799.00 1 hr & 20 mins)",
                    "Hot Compress or Ear Candle w/ full body massage" => "Hot Compress or Ear Candle w/ full body massage (₱449 1 hr & 10 mins)"
                );

                $services = array();
                $therapists = array();
                
                // Collect all services and therapists
                foreach($patient as $key => $value) {
                    if(strpos($key, 'service_') === 0) {
                        $index = substr($key, 8); // Remove 'service_' prefix
                        $services[$index] = $value;
                    }
                    if(strpos($key, 'therapist_') === 0) {
                        $index = substr($key, 10); // Remove 'therapist_' prefix
                        $therapists[$index] = $value;
                    }
                }

                // Sort by index to maintain order
                ksort($services);
                ksort($therapists);

                if(!empty($services)) {
                    foreach($services as $index => $service) {
                        echo "<tr>";
                        echo "<td>" . (isset($service_details[$service]) ? $service_details[$service] : $service) . "</td>";
                        echo "<td>" . (isset($therapists[$index]) ? $therapists[$index] : 'N/A') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Fallback for old data format
                    if(isset($patient['service'])) {
                        echo "<tr>";
                        echo "<td>" . (isset($service_details[$patient['service']]) ? $service_details[$patient['service']] : $patient['service']) . "</td>";
                        echo "<td>" . (isset($patient['therapist']) ? $patient['therapist'] : 'N/A') . "</td>";
                        echo "</tr>";
                    } else {
                        echo "<tr><td colspan='2' class='text-center'>No services selected</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <p><b>Status:</b>
        <?php 
        switch($status){ 
            case(0): 
                echo '<span class="badge badge-primary">Pending</span>';
            break; 
            case(1): 
            echo '<span class="badge badge-success">Confirmed</span>';
            break; 
            case(2): 
                echo '<span class="badge badge-danger">Cancelled</span>';
            break; 
            default: 
                echo '<span class="badge badge-secondary">NA</span>';
        }
        ?>
    
    </p>
</div>
<div class="modal-footer border-0">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
   
   