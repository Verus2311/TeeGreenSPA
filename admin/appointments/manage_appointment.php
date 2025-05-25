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
    padding-top:0 !important;
}
</style>
<div class="container-fluid">
    <form id="appointment_form" class="py-2">
    <div class="row" id="appointment">
        <div class="col-6" id="frm-field">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <input type="hidden" name="patient_id" value="<?php echo isset($patient_id) ? $patient_id : '' ?>">
                <div class="form-group">
                    <label for="name" class="control-label">Fullname</label>
                    <input type="text" class="form-control" name="name" value="<?php echo isset($patient['name']) ? $patient['name'] : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="Age" class="control-label">Age</label>
                    <input type="Age" class="form-control" name="Age" value="<?php echo isset($patient['Age']) ? $patient['Age'] : '' ?>"  required>
                </div>
                
                <div class="form-group">
                    <label for="gender" class="control-label">Gender</label>
                    <select type="text" class="custom-select" name="gender" required>
                    <option <?php echo isset($patient['gender']) && $patient['gender'] == "Male" ? "selected": "" ?>>Male</option>
                    <option <?php echo isset($patient['gender']) && $patient['gender'] == "Female" ? "selected": "" ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dob" class="control-label">Date of Birth</label>
                    <input type="date" class="form-control" name="dob" value="<?php echo isset($patient['dob']) ? $patient['dob'] : '' ?>"  required>
                </div>
        </div>
        <div class="col-6">
                
                <div class="form-group">
                    <label for="address" class="control-label">Address</label>
                    <textarea class="form-control" name="address" rows="3" required><?php echo isset($patient['address']) ? $patient['address'] : '' ?></textarea>
                </div>

            <div class="form-group">
                <label class="control-label">Services to be availed (Maximum of 3 Services)</label>
                <div id="services-container">
                    <div class="service-entry mb-2">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="custom-select service-select" name="services[]" required>
                                    <option value="">Select Service</option>
                                    <option value="Triple Bogey Strokes">Triple Bogey Strokes (1hr & 15 mins ₱599.00)</option>
                                    <option value="Hole-in-One Strokes">Hole-in-One Strokes (50 mins ₱399.00)</option>
                                    <option value="Par Strokes">Par Strokes (50 mins ₱350.00)</option>
                                    <option value="Eagle Strokes">Eagle Strokes (50 mins ₱599.00)</option>
                                    <option value="Condor Stroke">Condor Stroke (2 hours ₱999.00)</option>
                                    <option value="Body Scrub">Body Scrub (30mins ₱350.00)</option>
                                    <option value="Body Scrub w/ full body massage">Body Scrub w/ full body massage (₱799.00 1 hr & 20 mins)</option>
                                    <option value="Hot Compress or Ear Candle w/ full body massage">Hot Compress or Ear Candle w/ full body massage (₱449 1 hr & 10 mins)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="custom-select therapist-select" name="therapists[]" required>
                                    <option value="">Select Therapist</option>
                                    <option value="Valerie">Valerie</option>
                                    <option value="Vanessa">Vanessa</option>
                                    <option value="Satchie">Satchie</option>
                                    <option value="Bella">Bella</option>
                                    <option value="Kimmy">Kimmy</option>
                                    <option value="Xander">Xander</option>
                                    <option value="ian">ian</option>
                                    <option value="MC">MC</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm remove-service" style="display:none;">-</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-success btn-sm mt-2" id="add-service">+ Add Another Service</button>
            </div>
                <div class="form-group">
                <label for="ailment" class="control-label">Ailment</label>
                <textarea class="form-control" name="ailment" rows="3" required><?php echo isset($ailment)? $ailment : "" ?></textarea>
            </div>
                
            
<?php if($_settings->userdata('id') > 0): ?>
            <div class="form-group">
                <label for="status" class="control-label">Status</label>
                <select name="status" id="status" class="custom custom-select">
                    <option value="0"<?php echo isset($status) && $status == "0" ? "selected": "" ?>>Pending</option>
                    <option value="1"<?php echo isset($status) && $status == "1" ? "selected": "" ?>>Confirmed</option>
                    <option value="2"<?php echo isset($status) && $status == "2" ? "selected": "" ?>>Cancelled</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="status" value="0">
            <?php endif; ?>

<?php
if (isset($patient['services'])) {
    echo "<p><strong>Selected Service:</strong> " . htmlspecialchars($patient['services']) . "</p>";
} else {

}
?>

            <div class="form-group">
                <label for="date_sched" class="control-label">Appointment</label>
                <input type="datetime-local" class="form-control" name="date_sched" value="<?php echo isset($date_sched)? date("Y-m-d\TH:i",strtotime($date_sched)) : "" ?>" required>
            </div>
            <?php if($_settings->userdata('id') > 0): ?>
            <div class="form-group">
                
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="status" value="0">
            <?php endif; ?>
        </div>
        <div class="form-group d-flex justify-content-end w-100 form-group">
            <button class="btn-primary btn">Submit Appointment</button>
            <button class="btn-light btn ml-2" type="button" data-dismiss="modal">Cancel</button>
        </div>
        </form>
    </div>
</div>
<script>
$(function(){
    $('#appointment_form').submit(function(e){
        e.preventDefault();
        var _this = $(this);
        $('.err-msg').remove();
        
        // Check for therapist time conflicts
        var dateSched = $('input[name="date_sched"]').val();
        var therapists = [];
        var services = [];
        
        // Collect all therapists and services
        $('.service-entry').each(function() {
            var therapist = $(this).find('.therapist-select').val();
            var service = $(this).find('.service-select').val();
            if(therapist && service) {
                therapists.push(therapist);
                services.push(service);
            }
        });
        
        // Validate that all entries have both service and therapist
        if(therapists.length === 0 || services.length === 0) {
            alert_toast("Please select both service and therapist for all entries", 'error');
            return false;
        }
        
        if ($('.service-entry').length > 3) {
            alert_toast("You can only avail a maximum of 3 services.", 'warning');
            return false;
        }
        
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=check_therapist_availability",
            data: {
                date_sched: dateSched,
                therapists: therapists,
                services: services,
                appointment_id: $('input[name="id"]').val()
            },
            method: 'POST',
            dataType: 'json',
            error: function(err) {
                console.log(err);
                alert_toast("An error occurred", 'error');
                end_loader();
            },
            success: function(resp) {
                if (resp.status === 'conflict') {
                    alert_toast("Error: " + resp.message, 'error');
                    end_loader();
                    return false;
                }
                
                // If no conflicts, proceed with form submission
                var formData = new FormData(_this[0]);
                
                $.ajax({
                    url: _base_url_ + "classes/Master.php?f=save_appointment",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    method: 'POST',
                    type: 'POST',
                    dataType: 'json',
                    error: function(err) {
                        console.log(err);
                        alert_toast("An error occurred", 'error');
                        end_loader();
                    },
                    success: function(resp) {
                        if (typeof resp == 'object' && resp.status == 'success') {
                            location.reload();
                        } else if (resp.status == 'failed' && !!resp.msg) {
                            var el = $('<div>');
                            el.addClass("alert alert-danger err-msg").text(resp.msg);
                            _this.prepend(el);
                            el.show('slow');
                            $("html, body").animate({ scrollTop: $('#uni_modal').offset().top }, "fast");
                        } else {
                            alert_toast("An error occurred", 'error');
                            console.log(resp);
                        }
                        end_loader();
                    }
                });
            }
        });
    });

    $('#uni_modal').on('hidden.bs.modal', function (e) {
        if($('#appointment_form').length <= 0)
            location.reload()
    })

    // Add new service entry
    $('#add-service').click(function(){
        if ($('.service-entry').length >= 3) {
            alert_toast("You can only avail a maximum of 3 services.", 'warning');
            return;
        }
        var newEntry = $('.service-entry:first').clone();
        newEntry.find('select').val('');
        newEntry.find('.remove-service').show();
        $('#services-container').append(newEntry);
    });

    // Remove service entry
    $(document).on('click', '.remove-service', function(){
        $(this).closest('.service-entry').remove();
    });

    // Show remove button for first entry if there are multiple entries
    function updateRemoveButtons() {
        if($('.service-entry').length > 1) {
            $('.remove-service').show();
        } else {
            $('.remove-service').hide();
        }
    }

    // Initialize remove buttons
    updateRemoveButtons();
})
</script>


