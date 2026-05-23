<?php
require '../db.php';

// Get all regions for dropdown
$regions = [];
$regionStmt = $conn->prepare("SELECT regionID, regionName FROM regions ORDER BY regionName ASC");
if ($regionStmt) {
    $regionStmt->execute();
    $regionRes = $regionStmt->get_result();
    while ($row = $regionRes->fetch_assoc()) {
        $regions[] = $row;
    }
    $regionStmt->close();
}

// Fetch all gas batches with related information
$gases = [];
$result = $conn->query("
    SELECT 
        gb.batch_id,
        gb.gas_id,
        gb.batchName,
        gb.regionID,
        gb.quantity,
        gb.available,
        gb.unit_price,
        gb.total_price,
        gb.createdAt,
        gm.gas_name,
        gm.quantity as total_quantity,
        gm.unit_price as avg_unit_price,
        r.regionName
    FROM gas_batch_details gb
    LEFT JOIN gas_master gm ON gb.gas_id = gm.gas_id
    LEFT JOIN regions r ON gb.regionID = r.regionID
    ORDER BY gb.batch_id DESC
");
if ($result) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $row['serial'] = $counter++;
        $gases[] = $row;
    }
}
?>

<div class="form-container">
    <h2>Add New Gas</h2>
    <form id="addGasForm">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="gasName" class="form-label">Gas Name <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="gasName" name="gasName" placeholder="e.g., R410A, R22" required>
                    <small id="gasNameError" class="text-danger" style="display: none;"></small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="batchName" class="form-label">Batch Name <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" id="batchName" name="batchName" placeholder="e.g., May 2026, Summer Batch" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="regionID" class="form-label">Region <span style="color: red;">*</span></label>
                    <select class="form-control select2" id="regionID" name="regionID" required>
                        <option value="">-- Select Region --</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region['regionID']; ?>" <?php echo ($region['regionID'] == 4) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($region['regionName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity (Kilos) <span style="color: red;">*</span></label>
                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="0.00" step="0.01" min="0" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="unitPrice" class="form-label">Unit Price (per Kilo) <span style="color: red;">*</span></label>
                    <input type="number" class="form-control" id="unitPrice" name="unitPrice" placeholder="0.00" step="0.01" min="0" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="totalPrice" class="form-label">Total Price (Auto Calculated)</label>
                    <input type="number" class="form-control" id="totalPrice" name="totalPrice" placeholder="0.00" step="0.01" readonly style="background-color: #f5f5f5;">
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Add Gas</button>
            <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
    </form>
</div>

<!-- Display Gases Table -->
<div class="gases-table" style="margin-top: 40px;">
    <h3>Existing Gas Batches</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Gas Name</th>
                    <th>Batch Name</th>
                    <th>Region</th>
                    <th>Batch Qty</th>
                    <th>Available</th>
                    <th>Unit Price</th>
                    <th>Batch Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="gasesTableBody">
                <?php foreach ($gases as $gas): ?>
                <?php 
                    $createdDate = date('Y-m-d', strtotime($gas['createdAt']));
                    $today = date('Y-m-d');
                    $canDelete = ($createdDate === $today);
                ?>
                <tr style="border-bottom: 1px solid #e0e0e0;">
                    <td><?php echo $gas['serial']; ?></td>
                    <td><strong><?php echo htmlspecialchars($gas['gas_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($gas['batchName']); ?></td>
                    <td><?php echo htmlspecialchars($gas['regionName']); ?></td>
                    <td><?php echo number_format($gas['quantity'], 2); ?></td>
                    <td><span style="color: #11998e; font-weight: 600;"><?php echo number_format($gas['available'], 2); ?></span></td>
                    <td><?php echo number_format($gas['unit_price'], 2); ?></td>
                    <td><?php echo number_format($gas['total_price'], 2); ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewGasDetails(<?php echo htmlspecialchars(json_encode($gas)); ?>)" style="padding: 6px 12px; font-size: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;">View</button>
                        <button class="btn btn-sm btn-warning" onclick="openUpdateGasModal(<?php echo htmlspecialchars(json_encode($gas)); ?>)" style="padding: 6px 12px; font-size: 12px; background: #f39c12; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;">Update</button>
                        <?php if ($canDelete): ?>
                        <button class="btn btn-sm btn-danger" onclick="deleteGasBatch(<?php echo $gas['batch_id']; ?>)" style="padding: 6px 12px; font-size: 12px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($gases)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #999;">No gas batches found. Add one to get started!</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Gas Details Modal -->
<div id="viewGasModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto;">
    <div class="form-container" style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 2% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="top: 0; background: white; padding: 20px; border-bottom: 2px solid #f0f0f0; border-radius: 16px 16px 0 0; z-index: 10;">
            <h2 style="margin: 0; color: #667eea;">Gas Batch Details</h2>
            <button onclick="closeViewGasModal()" style="position: absolute; right: 20px; top: 20px; background: #ff4757; color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div id="gasDetailsContent" style="padding: 20px;">
            <!-- Gas details will be inserted here -->
        </div>
        <div style="bottom: 0; background: white; padding: 20px; border-top: 2px solid #f0f0f0; text-align: center; border-radius: 0 0 16px 16px;">
            <button class="btn btn-secondary" onclick="closeViewGasModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<!-- Update Gas Modal -->
<div id="updateGasModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); overflow: auto;">
    <div class="form-container" style="max-width: 600px; margin: 5% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 16px 16px 0 0;">
            <h2 style="margin: 0; color: white;">🔄 Update Gas Batch</h2>
            <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Add new batch for <span id="updateGasName" style="font-weight: 600;"></span></p>
            <button onclick="closeUpdateGasModal()" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <form id="updateGasForm" style="padding: 30px;">
            <input type="hidden" id="updateGasId" name="gas_id">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="updateBatchNameGas" class="form-label">Batch Name</label>
                <input type="text" id="updateBatchNameGas" name="batchName" class="form-control" placeholder="Enter batch name" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="updateQuantityGas" class="form-label">Quantity to Add (Kilos)</label>
                <input type="number" id="updateQuantityGas" name="quantity" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                <small style="color: #666; font-size: 12px;">This will be added to current quantity</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="updateUnitPriceGas" class="form-label">Unit Price (per Kilo)</label>
                <input type="number" id="updateUnitPriceGas" name="unitPrice" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                <small style="color: #666; font-size: 12px;">Average unit price will be recalculated</small>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #666;">Current Total Qty:</span>
                    <span style="font-weight: 600;" id="currentTotalQuantity">0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #666;">Current Avg Price:</span>
                    <span style="font-weight: 600;" id="currentAvgPrice">0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #666;">Current Total Amount:</span>
                    <span style="font-weight: 600;" id="currentTotalAmount">0.00</span>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center;">
                <button type="submit" class="btn btn-success" style="padding: 12px 30px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none; border-radius: 6px; cursor: pointer;">Update Batch</button>
                <button type="button" class="btn btn-secondary" onclick="closeUpdateGasModal()" style="padding: 12px 30px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#regionID').select2({
        placeholder: "-- Select Region --",
        allowClear: false,
        width: '100%'
    });

    // Calculate total price on input change
    $('#quantity, #unitPrice').on('input', function() {
        const quantity = parseFloat($('#quantity').val()) || 0;
        const unitPrice = parseFloat($('#unitPrice').val()) || 0;
        const totalPrice = (quantity * unitPrice).toFixed(2);
        $('#totalPrice').val(totalPrice);
    });

    // Form submission
    $('#addGasForm').on('submit', function(e) {
        e.preventDefault();

        const gasName = $('#gasName').val().trim();
        const batchName = $('#batchName').val().trim();
        const regionID = $('#regionID').val();
        const quantity = parseFloat($('#quantity').val());
        const unitPrice = parseFloat($('#unitPrice').val());
        const totalPrice = parseFloat($('#totalPrice').val());

        // Validation
        if (!gasName) {
            showError('gasName', 'Gas name is required');
            return;
        }

        if (!batchName) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Batch name is required'
            });
            return;
        }

        if (!regionID) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select a region'
            });
            return;
        }

        if (quantity <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Quantity must be greater than 0'
            });
            return;
        }

        if (unitPrice <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unit price must be greater than 0'
            });
            return;
        }

        // Submit via AJAX
        $.ajax({
            url: 'backend/saveGas.php',
            type: 'POST',
            data: {
                gasName: gasName,
                batchName: batchName,
                regionID: regionID,
                quantity: quantity,
                unitPrice: unitPrice,
                totalPrice: totalPrice
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Gas added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#addGasForm')[0].reset();
                    $('#totalPrice').val('0.00');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to add gas'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Server error while adding gas'
                });
            }
        });
    });

    function showError(fieldId, message) {
        const errorElement = $('#' + fieldId + 'Error');
        errorElement.text(message).show();
        $('#' + fieldId).focus();
        
        setTimeout(() => {
            errorElement.hide();
        }, 5000);
    }

    // Clear error when user starts typing
    $('#gasName').on('input', function() {
        $('#gasNameError').hide();
    });

    // View Gas Details
    window.viewGasDetails = function(gas) {
        const detailsHtml = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; color: white;">
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Gas Name</p>
                        <p style="margin: 0; font-size: 18px; font-weight: 600;">${gas.gas_name}</p>
                    </div>
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Batch Name</p>
                        <p style="margin: 0; font-size: 18px; font-weight: 600;">${gas.batchName}</p>
                    </div>
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Region</p>
                        <p style="margin: 0; font-size: 18px; font-weight: 600;">${gas.regionName}</p>
                    </div>
                    <div>
                        <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Available Qty</p>
                        <p style="margin: 0; font-size: 18px; font-weight: 600;">${parseFloat(gas.available).toFixed(2)} kg</p>
                    </div>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                        <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">Batch Quantity</p>
                        <p style="margin: 0; font-size: 16px; font-weight: 600;">${parseFloat(gas.quantity).toFixed(2)} kg</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                        <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">Unit Price</p>
                        <p style="margin: 0; font-size: 16px; font-weight: 600;">RS ${parseFloat(gas.unit_price).toFixed(2)}</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #11998e;">
                        <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">Total Master Qty</p>
                        <p style="margin: 0; font-size: 16px; font-weight: 600;">${parseFloat(gas.total_quantity).toFixed(2)} kg</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #11998e;">
                        <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">Avg Unit Price</p>
                        <p style="margin: 0; font-size: 16px; font-weight: 600;">RS ${parseFloat(gas.avg_unit_price).toFixed(2)}</p>
                    </div>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 15px; border-radius: 8px; color: white;">
                    <p style="margin: 0 0 5px 0; font-size: 12px; opacity: 0.9;">Batch Total Price</p>
                    <p style="margin: 0; font-size: 20px; font-weight: 600;">RS ${parseFloat(gas.total_price).toFixed(2)}</p>
                </div>
            </div>
        `;
        $('#gasDetailsContent').html(detailsHtml);
        $('#viewGasModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    };

    // Close View Gas Modal
    window.closeViewGasModal = function() {
        $('#viewGasModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    };

    // Open Update Gas Modal
    window.openUpdateGasModal = function(gas) {
        $('#updateGasId').val(gas.gas_id);
        $('#updateGasName').text(gas.gas_name);
        $('#updateBatchNameGas').val('');
        $('#updateQuantityGas').val('');
        $('#updateUnitPriceGas').val('');
        
        $('#currentTotalQuantity').text(parseFloat(gas.total_quantity).toFixed(2));
        $('#currentAvgPrice').text(parseFloat(gas.avg_unit_price).toFixed(2));
        $('#currentTotalAmount').text(parseFloat(gas.total_quantity * gas.avg_unit_price).toFixed(2));
        
        $('#updateGasModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    };

    // Close Update Gas Modal
    window.closeUpdateGasModal = function() {
        $('#updateGasModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    };

    // Handle Update Gas Form Submission
    $('#updateGasForm').on('submit', function(e) {
        e.preventDefault();
        
        const gasId = $('#updateGasId').val();
        const batchName = $('#updateBatchNameGas').val().trim();
        const quantity = parseFloat($('#updateQuantityGas').val());
        const unitPrice = parseFloat($('#updateUnitPriceGas').val());
        
        if (!batchName || quantity <= 0 || unitPrice <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill all fields with valid values'
            });
            return;
        }
        
        $.ajax({
            url: 'backend/updateGasBatch.php',
            type: 'POST',
            data: {
                gas_id: gasId,
                batchName: batchName,
                quantity: quantity,
                unitPrice: unitPrice
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    closeUpdateGasModal(); // Close modal first
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Gas batch updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to update gas batch',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Full Response:', xhr.responseText);
                console.error('Status:', status);
                console.error('Error:', error);
                
                let errorMessage = 'Server error while updating gas batch';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch(e) {
                    errorMessage = xhr.responseText || errorMessage;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error!',
                    html: `<div style="text-align: left; word-wrap: break-word;">
                        <p><strong>Error Details:</strong></p>
                        <p style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">
                            ${errorMessage}
                        </p>
                    </div>`,
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Delete Gas Batch
    window.deleteGasBatch = function(batchId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'backend/deleteGasBatch.php',
                    type: 'POST',
                    data: { batch_id: batchId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Gas batch deleted successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to delete gas batch'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Server error while deleting gas batch'
                        });
                    }
                });
            }
        });
    };

    // Close modals when clicking outside
    $(document).on('click', '#viewGasModal', function(e) {
        if (e.target.id === 'viewGasModal') {
            closeViewGasModal();
        }
    });

    $(document).on('click', '#updateGasModal', function(e) {
        if (e.target.id === 'updateGasModal') {
            closeUpdateGasModal();
        }
    });
});</script>

<style>
    .form-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        max-width: 800px;
    }

    .form-container h2 {
        margin-bottom: 25px;
        color: #333;
        font-size: 24px;
        font-weight: 600;
    }

    .mb-3 {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 10px;
    }

    .col-md-6 {
        width: 100%;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        border: 1px solid #ddd;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    .text-danger {
        color: #d9534f;
        font-size: 12px;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .row {
            grid-template-columns: 1fr;
        }

        .form-container {
            padding: 20px;
        }
    }
</style>
