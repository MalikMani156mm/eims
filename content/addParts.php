<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

// Fetch all brands for dropdown
$brands = [];
$brandsResult = $conn->query("SELECT * FROM brands ORDER BY brandName ASC");
if ($brandsResult) {
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = $row;
    }
}
?>

<div class="container">
    <div class="packages-table">
        <h3>➕ Add Parts</h3>

        <!-- Add Parts Form -->
        <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <form id="addPartsForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">

                    <div class="form-group">
                        <label for="partName" class="form-label" style="color: black;">Part Name *</label>
                        <input type="text" id="partName" name="partName" class="form-control" placeholder="Enter part name" required>
                        <small style="color: #999;">Must be unique</small>
                    </div>

                    <div class="form-group">
                        <label for="batchName" class="form-label" style="color: black;">Batch Name *</label>
                        <input type="text" id="batchName" name="batchName" class="form-control" placeholder="Enter batch name" required>
                    </div>

                    <div class="form-group">
                        <label for="brandID" class="form-label" style="color: black;">Brand *</label>
                        <select id="brandID" name="brandID" class="form-control" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['brandID']; ?>">
                                    <?php echo htmlspecialchars($brand['brandName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity" class="form-label" style="color: black;">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" placeholder="Enter quantity" min="1" value="1" required>
                    </div>

                </div>

                <div style="display: flex; gap: 12px; justify-content: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-size: 16px;">
                        Next: Add Serial Numbers
                    </button>
                    <button type="reset" class="btn btn-secondary" style="padding: 12px 40px; font-size: 16px;">
                        Clear
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Serial Number Modal -->
<div id="serialNumberModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); overflow: auto; padding: 20px;">
    <div style="max-width: 700px; max-height: 90vh; overflow-y: auto; margin: 3% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); pointer-events: auto;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0; position: sticky; top: 0; z-index: 10;">
            <h2 style="margin: 0; color: white;">🏷️ Add Serial Numbers</h2>
            <button type="button" onclick="closeSerialModal()" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; pointer-events: auto;">&times;</button>
        </div>

        <div style="padding: 30px;">
            <!-- Part Info Display -->
            <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #667eea;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 5px;">PART NAME</div>
                        <div style="font-size: 16px; font-weight: 600; color: #333;" id="displayPartName">-</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 5px;">BATCH NAME</div>
                        <div style="font-size: 16px; font-weight: 600; color: #333;" id="displayBatchName">-</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 5px;">BRAND</div>
                        <div style="font-size: 16px; font-weight: 600; color: #333;" id="displayBrandName">-</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #999; margin-bottom: 5px;">QUANTITY</div>
                        <div style="font-size: 16px; font-weight: 600; color: #667eea;" id="displayQuantity">-</div>
                    </div>
                </div>
            </div>

            <!-- Option Selection -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 25px; pointer-events: auto;">
                <div style="padding: 20px; border: 2px solid #667eea; border-radius: 12px; background: #f8f9ff;">
                    <div style="font-size: 24px; margin-bottom: 10px;">📦</div>
                    <div style="font-size: 16px; font-weight: 600; margin-bottom: 5px;">Multiple Unique Serials</div>
                    <p style="margin: 0; font-size: 14px; color: #999;">Assign different serial numbers to each part</p>
                </div>
            </div>

            <!-- Multiple Serials Input -->
            <div id="multipleSerialSection" style="margin-bottom: 25px;">
                <label class="form-label" style="font-size: 16px; margin-bottom: 15px; display: block;">Serial Numbers *</label>
                <div id="serialInputsContainer" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                    <!-- Serial inputs will be generated here -->
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 12px; justify-content: center; pointer-events: auto;">
                <button type="button" class="btn btn-primary" onclick="submitSerialNumbers()" style="padding: 12px 40px; font-size: 16px; pointer-events: auto; cursor: pointer;">
                    ✅ Save Parts
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeSerialModal()" style="padding: 12px 40px; font-size: 16px; pointer-events: auto; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let formData = {};

    $(document).on('submit', '#addPartsForm', function(e) {
        e.preventDefault();

        formData = {
            partName: $('#partName').val().trim(),
            batchName: $('#batchName').val().trim(),
            brandID: parseInt($('#brandID').val()),
            quantity: parseInt($('#quantity').val())
        };

        if (!formData.partName) {
            Swal.fire('Error', 'Part name is required', 'error');
            return;
        }

        if (!formData.batchName) {
            Swal.fire('Error', 'Batch name is required', 'error');
            return;
        }

        if (formData.brandID <= 0) {
            Swal.fire('Error', 'Brand is required', 'error');
            return;
        }

        if (formData.quantity <= 0) {
            Swal.fire('Error', 'Quantity must be greater than 0', 'error');
            return;
        }

        // Ask whether this part has serial numbers
        Swal.fire({
            title: 'Does this part have serial numbers?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, add serials',
            cancelButtonText: 'No, skip serials',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                openSerialModal();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Save as single record with quantity
                savePartsNoSerials();
            }
        });
    });

    function openSerialModal() {
        $('#displayPartName').text(formData.partName);
        $('#displayBatchName').text(formData.batchName);
        const brandText = $('#brandID option:selected').text();
        $('#displayBrandName').text(brandText);
        $('#displayQuantity').text(formData.quantity);

        // Generate input fields for multiple serials
        const container = $('#serialInputsContainer');
        container.empty();

        for (let i = 1; i <= formData.quantity; i++) {
            const input = `
                <div class="form-group">
                    <label class="form-label" style="font-size: 14px;">Serial #${i} *</label>
                    <input type="text" class="serial-input form-control" data-index="${i}" placeholder="Serial number for part ${i}" required>
                </div>
            `;
            container.append(input);
        }

        // Focus on first input
        $('.serial-input').first().focus();

        $('#serialNumberModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeSerialModal() {
        $('#serialNumberModal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }

    function submitSerialNumbers() {
        let serialNumbers = [];

        // Collect multiple serial numbers
        $('.serial-input').each(function() {
            const serial = $(this).val().trim();
            if (!serial) {
                Swal.fire('Error', 'All serial numbers are required', 'error');
                return false;
            }
            serialNumbers.push(serial);
        });

        if (serialNumbers.length !== formData.quantity) {
            Swal.fire('Error', 'Please fill all serial number fields', 'error');
            return;
        }

        // Check for duplicate serials
        const uniqueSerials = new Set(serialNumbers);
        if (uniqueSerials.size !== serialNumbers.length) {
            Swal.fire('Error', 'Serial numbers must be unique', 'error');
            return;
        }

        // Send to backend
        savePartsWithSerials(serialNumbers);
    }

    function savePartsNoSerials() {
        // confirm
        Swal.fire({
            title: 'Confirm',
            text: `Save ${formData.quantity} item(s) without serial numbers?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, save',
            cancelButtonText: 'Cancel'
        }).then((res) => {
            if (!res.isConfirmed) return;

            $.ajax({
                url: 'backend/saveParts.php',
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    partName: formData.partName,
                    batchName: formData.batchName,
                    brandID: formData.brandID,
                    quantity: formData.quantity,
                    noSerial: true
                }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                        $('#addPartsForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'An error occurred while saving parts', 'error');
                }
            });
        });
    }

    function savePartsWithSerials(serialNumbers) {
        $.ajax({
            url: 'backend/saveParts.php',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                partName: formData.partName,
                batchName: formData.batchName,
                brandID: formData.brandID,
                quantity: formData.quantity,
                serialNumbers: serialNumbers
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    closeSerialModal();
                    $('#addPartsForm')[0].reset();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while saving parts', 'error');
            }
        });
    }

    // Close modal when clicking outside
    $(document).on('click', '#serialNumberModal', function(e) {
        if (e.target.id === 'serialNumberModal') {
            closeSerialModal();
        }
    });
</script>