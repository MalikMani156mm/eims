<?php
require '../adminAuth.php';
require '../db.php';

// Fetch all active distributing officers with their pending amounts
$officers = [];
if (isset($adminRole) && $adminRole === 'user') {
    $stmt = $conn->prepare(
        "SELECT 
            d.DO_ID, 
            d.DO_Name, 
            d.CNIC,
            COALESCE(SUM(CASE WHEN s.paymentStatus IN ('pending', 'partial') THEN s.pendingAmount ELSE 0 END), 0) as pendingAmount
        FROM distributing_officer d
        LEFT JOIN do_sales s ON d.DO_ID = s.DO_ID
        WHERE d.status = 0 AND d.regionID = ?
        GROUP BY d.DO_ID, d.DO_Name, d.CNIC
        ORDER BY d.DO_Name ASC"
    );
    $stmt->bind_param('i', $regionID);
    $stmt->execute();
    $officersResult = $stmt->get_result();
    if ($officersResult) {
        while ($row = $officersResult->fetch_assoc()) {
            $officers[] = $row;
        }
    }
    $stmt->close();
} else {
    $officersResult = $conn->query(
        "SELECT 
            d.DO_ID, 
            d.DO_Name, 
            d.CNIC,
            COALESCE(SUM(CASE WHEN s.paymentStatus IN ('pending', 'partial') THEN s.pendingAmount ELSE 0 END), 0) as pendingAmount
        FROM distributing_officer d
        LEFT JOIN do_sales s ON d.DO_ID = s.DO_ID
        WHERE d.status = 0
        GROUP BY d.DO_ID, d.DO_Name, d.CNIC
        ORDER BY d.DO_Name ASC"
    );
    if ($officersResult) {
        while ($row = $officersResult->fetch_assoc()) {
            $officers[] = $row;
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/select2.min.css">
<script src="assets/js/select2.min.js"></script>

<style>
    .dispatch-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .dispatch-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .dispatch-header h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .card-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
        color: #333;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #555;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-scan {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .btn-scan:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
    }
    
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .items-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }
    
    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    
    .items-table tr:hover {
        background: #f8f9ff;
    }
    
    .item-input {
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 100px;
        text-align: right;
    }
    
    .btn-remove {
        background: #f44336;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .summary-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 16px;
    }
    
    .summary-row.total {
        font-size: 24px;
        font-weight: bold;
        border-top: 2px solid rgba(255,255,255,0.3);
        margin-top: 10px;
        padding-top: 15px;
    }
    
    .payment-card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        border: 2px solid #667eea;
        margin-top: 20px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 40px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        font-size: 18px;
        width: 100%;
        margin-top: 20px;
        transition: all 0.3s;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid #17a2b8;
    }
    
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .badge-success {
        background: #4caf50;
        color: white;
    }
    
    .badge-warning {
        background: #ff9800;
        color: white;
    }
    
    .badge-info {
        background: #17a2b8;
        color: white;
    }
</style>

<div class="dispatch-container">
    <div class="dispatch-header">
        <h2>📦 Dispatch to Distributing Officer</h2>
        <p style="margin: 0; opacity: 0.9;">Scan products and create distribution record</p>
    </div>

    <form id="dispatchForm">
        <!-- DO Selection Card -->
        <div class="form-card">
            <div class="card-title">👤 Select Distributing Officer</div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="DO_ID">Distributing Officer *</label>
                    <select id="DO_ID" name="DO_ID" class="form-control" required>
                        <option value="">Select Distributing Officer</option>
                        <?php foreach ($officers as $officer): ?>
                            <?php 
                            $hasPending = $officer['pendingAmount'] > 0;
                            $pendingText = $hasPending ? 'Pending: RS ' . number_format($officer['pendingAmount'], 0) : 'No pending amount';
                            ?>
                            <option value="<?php echo $officer['DO_ID']; ?>">
                                <?php echo htmlspecialchars($officer['DO_Name']); ?> (CNIC: <?php echo htmlspecialchars($officer['CNIC']); ?>) - <?php echo $pendingText; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="saleDate">Dispatch Date</label>
                    <input type="date" id="saleDate" name="saleDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
        </div>

        <!-- Product Scan Card -->
        <div class="form-card">
            <div class="card-title">🔍 Scan Products</div>
            
            <div class="alert-info">
                💡 <strong>Tip:</strong> Scan barcode or enter serial number manually. Press Enter or click Add to add product.
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="serialNumber">Serial Number</label>
                    <input type="text" id="serialNumber" class="form-control" placeholder="Scan or enter serial number" autocomplete="off">
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn-scan" onclick="addProductBySerial()">➕ Add Product</button>
                </div>
            </div>
            
            <div id="scanStatus" style="margin-top: 10px; display: none;"></div>
        </div>

        <!-- Products Table Card -->
        <div class="form-card" id="itemsCard" style="display: none;">
            <div class="card-title">
                📋 Scanned Products 
                <span class="badge badge-info" id="itemCount">0 items</span>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Serial Number</th>
                            <th>Product</th>
                            <th>Batch</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Discount %</th>
                            <th>Discount Amt</th>
                            <th>Final Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <!-- Items will be added here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary and Payment Card -->
        <div id="summaryCard" style="display: none;">
            <div class="summary-card">
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <span id="totalAmount">RS 0.00</span>
                </div>
                <div class="summary-row">
                    <span>Total Discount:</span>
                    <span id="totalDiscount">RS 0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Grand Total:</span>
                    <span id="grandTotal">RS 0.00</span>
                </div>
            </div>
            
            <div class="payment-card">
                <div class="card-title">💰 Payment Details</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="amountPaid">Amount Paid *</label>
                        <input type="number" id="amountPaid" name="amountPaid" class="form-control" placeholder="0.00" step="0.01" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Pending Amount</label>
                        <input type="text" id="pendingAmount" class="form-control" readonly style="background: #f5f5f5; font-weight: bold; color: #f44336;">
                    </div>
                    
                    <div class="form-group">
                        <label for="paymentDays">Payment Days (Max 45) *</label>
                        <input type="number" id="paymentDays" name="paymentDays" class="form-control" placeholder="0" min="0" max="45" value="0">
                    </div>
                </div>
                
                <div id="dueDateDisplay" style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 6px; display: none;">
                    <strong>Due Date:</strong> <span id="dueDate"></span>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                💾 Save Dispatch & Generate Receipt
            </button>
        </div>
    </form>
</div>

<script>
    var scannedItems = [];
    var itemCounter = 0;
    
    $(document).ready(function() {
        // Initialize Select2 for DO dropdown
        $('#DO_ID').select2({
            placeholder: 'Select Distributing Officer',
            allowClear: true,
            width: '100%'
        });
        
        // Focus on serial number input
        $('#serialNumber').focus();
        
        // Handle Enter key on serial number input
        $('#serialNumber').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                addProductBySerial();
            }
        });
        
        // Calculate pending amount when amount paid changes
        $('#amountPaid').on('input', function() {
            calculatePendingAmount();
        });
        
        // Calculate due date when payment days or sale date changes
        $('#paymentDays').on('input', function() {
            calculateDueDate();
        });
        
        $('#saleDate').on('change', function() {
            calculateDueDate();
        });
        
        // Form submission
        $('#dispatchForm').on('submit', function(e) {
            e.preventDefault();
            submitDispatch();
        });
    });
    
    function addProductBySerial() {
        const serialNumber = $('#serialNumber').val().trim();
        
        if (!serialNumber) {
            showStatus('Please enter a serial number', 'error');
            return;
        }
        
        // Check if already scanned
        if (scannedItems.some(item => item.serialNumber === serialNumber)) {
            showStatus('This product is already added!', 'error');
            $('#serialNumber').val('').focus();
            return;
        }
        
        // Show loading
        showStatus('Searching for serial number...', 'info');
        
        // Fetch product details by serial number
        $.ajax({
            url: 'backend/getProductBySerial.php',
            type: 'GET',
            data: { serialNumber: serialNumber },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    addItemToTable(response.data);
                    $('#serialNumber').val('').focus();
                    showStatus('Product added successfully!', 'success');
                } else {
                    showStatus(response.message || 'Serial number not found or not available', 'error');
                    $('#serialNumber').focus();
                }
            },
            error: function() {
                showStatus('Error fetching product details', 'error');
            }
        });
    }
    
    function addItemToTable(product) {
        itemCounter++;
        
        const item = {
            serialID: product.serialID,
            serialNumber: product.serialNumber,
            productID: product.productID,
            productName: product.productName,
            batchNumber: product.batchNumber,
            costPrice: parseFloat(product.cost || 0),
            sellingPrice: parseFloat(product.cost || 0),
            discountPercent: 0,
            discountAmount: 0,
            finalPrice: parseFloat(product.cost || 0)
        };
        
        scannedItems.push(item);
        
        const row = `
            <tr id="item-${itemCounter}">
                <td>${scannedItems.length}</td>
                <td><code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;">${item.serialNumber}</code></td>
                <td><strong>${item.productName}</strong></td>
                <td><span class="badge badge-info">${item.batchNumber}</span></td>
                <td>RS ${item.costPrice.toFixed(2)}</td>
                <td><input type="number" class="item-input" data-index="${scannedItems.length - 1}" data-field="sellingPrice" value="${item.sellingPrice}" step="0.01" min="0" onchange="updateItemPrice(this)"></td>
                <td><input type="number" class="item-input" data-index="${scannedItems.length - 1}" data-field="discountPercent" value="0" step="0.01" min="0" max="100" onchange="updateItemDiscount(this)" style="width: 80px;"></td>
                <td id="discount-${scannedItems.length - 1}">RS 0.00</td>
                <td id="final-${scannedItems.length - 1}" style="font-weight: bold; color: #11998e;">RS ${item.finalPrice.toFixed(2)}</td>
                <td><button type="button" class="btn-remove" onclick="removeItem(${scannedItems.length - 1})">✖ Remove</button></td>
            </tr>
        `;
        
        $('#itemsTableBody').append(row);
        $('#itemsCard').show();
        $('#summaryCard').show();
        updateItemCount();
        calculateTotals();
    }
    
    function updateItemPrice(input) {
        const index = parseInt($(input).data('index'));
        const newPrice = parseFloat($(input).val()) || 0;
        scannedItems[index].sellingPrice = newPrice;
        updateItemFinalPrice(index);
    }
    
    function updateItemDiscount(input) {
        const index = parseInt($(input).data('index'));
        const discountPercent = parseFloat($(input).val()) || 0;
        scannedItems[index].discountPercent = discountPercent;
        updateItemFinalPrice(index);
    }
    
    function updateItemFinalPrice(index) {
        const item = scannedItems[index];
        item.discountAmount = (item.sellingPrice * item.discountPercent) / 100;
        item.finalPrice = item.sellingPrice - item.discountAmount;
        
        $(`#discount-${index}`).text('RS ' + item.discountAmount.toFixed(2));
        $(`#final-${index}`).text('RS ' + item.finalPrice.toFixed(2));
        
        calculateTotals();
    }
    
    function removeItem(index) {
        scannedItems.splice(index, 1);
        renderItems();
    }
    
    function renderItems() {
        $('#itemsTableBody').empty();
        itemCounter = 0;
        
        scannedItems.forEach((item, index) => {
            itemCounter++;
            const row = `
                <tr id="item-${itemCounter}">
                    <td>${index + 1}</td>
                    <td><code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px;">${item.serialNumber}</code></td>
                    <td><strong>${item.productName}</strong></td>
                    <td><span class="badge badge-info">${item.batchNumber}</span></td>
                    <td>RS ${item.costPrice.toFixed(2)}</td>
                    <td><input type="number" class="item-input" data-index="${index}" data-field="sellingPrice" value="${item.sellingPrice}" step="0.01" min="0" onchange="updateItemPrice(this)"></td>
                    <td><input type="number" class="item-input" data-index="${index}" data-field="discountPercent" value="${item.discountPercent}" step="0.01" min="0" max="100" onchange="updateItemDiscount(this)" style="width: 80px;"></td>
                    <td id="discount-${index}">RS ${item.discountAmount.toFixed(2)}</td>
                    <td id="final-${index}" style="font-weight: bold; color: #11998e;">RS ${item.finalPrice.toFixed(2)}</td>
                    <td><button type="button" class="btn-remove" onclick="removeItem(${index})">✖ Remove</button></td>
                </tr>
            `;
            $('#itemsTableBody').append(row);
        });
        
        if (scannedItems.length === 0) {
            $('#itemsCard').hide();
            $('#summaryCard').hide();
        }
        
        updateItemCount();
        calculateTotals();
    }
    
    function updateItemCount() {
        $('#itemCount').text(scannedItems.length + ' item' + (scannedItems.length !== 1 ? 's' : ''));
    }
    
    function calculateTotals() {
        let totalAmount = 0;
        let totalDiscount = 0;
        
        scannedItems.forEach(item => {
            totalAmount += item.sellingPrice;
            totalDiscount += item.discountAmount;
        });
        
        const grandTotal = totalAmount - totalDiscount;
        
        $('#totalAmount').text('RS ' + totalAmount.toFixed(2));
        $('#totalDiscount').text('RS ' + totalDiscount.toFixed(2));
        $('#grandTotal').text('RS ' + grandTotal.toFixed(2));
        
        calculatePendingAmount();
    }
    
    function calculatePendingAmount() {
        const grandTotal = parseFloat($('#grandTotal').text().replace('RS ', '').replace(/,/g, '')) || 0;
        const amountPaid = parseFloat($('#amountPaid').val()) || 0;
        const pending = grandTotal - amountPaid;
        
        $('#pendingAmount').val('RS ' + pending.toFixed(2));
        
        if (pending > 0) {
            $('#pendingAmount').css('color', '#f44336');
        } else {
            $('#pendingAmount').css('color', '#4caf50');
        }
    }
    
    function calculateDueDate() {
        const days = parseInt($('#paymentDays').val()) || 0;
        const saleDate = $('#saleDate').val();
        
        if (days > 0 && saleDate) {
            const date = new Date(saleDate);
            date.setDate(date.getDate() + days);
            const formattedDate = date.toLocaleDateString('en-GB');
            
            $('#dueDate').text(formattedDate);
            $('#dueDateDisplay').show();
        } else {
            $('#dueDateDisplay').hide();
        }
    }
    
    function showStatus(message, type) {
        const colors = {
            success: '#4caf50',
            error: '#f44336',
            info: '#17a2b8'
        };
        
        $('#scanStatus').html(`
            <div style="padding: 10px; background: ${colors[type] || '#17a2b8'}; color: white; border-radius: 6px;">
                ${message}
            </div>
        `).show();
        
        setTimeout(() => {
            $('#scanStatus').fadeOut();
        }, 3000);
    }
    
    function submitDispatch() {
        // Validation
        if (!$('#DO_ID').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please select a Distributing Officer'
            });
            return;
        }
        
        if (scannedItems.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please add at least one product'
            });
            return;
        }
        
        const paymentDays = parseInt($('#paymentDays').val()) || 0;
        if (paymentDays > 45) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Payment days cannot exceed 45 days'
            });
            return;
        }
        
        // Prepare data
        const formData = {
            DO_ID: $('#DO_ID').val(),
            saleDate: $('#saleDate').val(),
            items: scannedItems,
            totalAmount: parseFloat($('#totalAmount').text().replace('RS ', '').replace(/,/g, '')),
            discountAmount: parseFloat($('#totalDiscount').text().replace('RS ', '').replace(/,/g, '')),
            grandTotal: parseFloat($('#grandTotal').text().replace('RS ', '').replace(/,/g, '')),
            amountPaid: parseFloat($('#amountPaid').val()) || 0,
            pendingAmount: parseFloat($('#pendingAmount').val().replace('RS ', '').replace(/,/g, '')),
            paymentDays: paymentDays
        };

        // Client-side validation: amount paid cannot be greater than grand total
        if (formData.amountPaid > formData.grandTotal) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Amount paid cannot be greater than total amount'
            });
            return;
        }
        
        // Submit via AJAX
        $.ajax({
            url: 'backend/saveDispatchToDO.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Dispatch created successfully!',
                        showCancelButton: true,
                        confirmButtonText: 'Create New',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reload the section so user can create a new dispatch
                            location.reload();
                        }
                        // If cancelled, do nothing and leave the page as-is
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to create dispatch'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to submit dispatch'
                });
            }
        });
    }
</script>
