<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';
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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
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
        border-color: #11998e;
        box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        background: #f0fff4;
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
        border: 2px solid #11998e;
        margin-top: 20px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
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
    
    .badge-info {
        background: #17a2b8;
        color: white;
    }
    
    .select2-container {
        width: 100% !important;
    }
</style>

<div class="dispatch-container">
    <div class="dispatch-header">
        <h2>📒 Dispatch to Ledger</h2>
        <p style="margin: 0; opacity: 0.9;">Scan products and create ledger distribution record</p>
    </div>

    <form id="dispatchForm">
        <!-- Ledger Selection Card -->
        <div class="form-card">
            <div class="card-title">📝 Ledger Information</div>
            
            <div class="alert-info">
                💡 <strong>Tip:</strong> Type new ledger name or select existing ledger from dropdown. Previous ledgers will auto-fill contact details.
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ledgerName">Ledger Name *</label>
                    <select id="ledgerName" name="ledgerName" class="form-control" required>
                        <option value="">Type new or select existing ledger</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="saleDate">Dispatch Date</label>
                    <input type="date" id="saleDate" name="saleDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ledgerCNIC">CNIC (Optional)</label>
                    <input type="text" id="ledgerCNIC" class="form-control" placeholder="xxxxx-xxxxxxx-x">
                </div>
                
                <div class="form-group">
                    <label for="ledgerContact">Contact Number (Optional)</label>
                    <input type="text" id="ledgerContact" class="form-control" placeholder="03xxxxxxxxx">
                </div>
                
                <div class="form-group">
                    <label for="ledgerAddress">Address (Optional)</label>
                    <input type="text" id="ledgerAddress" class="form-control" placeholder="Enter address">
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
    var ledgerData = {};
    
    $(document).ready(function() {
        // Initialize Select2 for ledger dropdown with tags (new ledgers can be typed)
        $('#ledgerName').select2({
            tags: true,
            placeholder: 'Type new ledger name or select existing...',
            allowClear: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term + ' (New Ledger)',
                    newTag: true
                };
            },
            insertTag: function(data, tag) {
                // Insert new tags at the top of the list
                data.unshift(tag);
            }
        });
        
        // Load existing ledgers
        loadLedgers();
        
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
        
        // Calculate due date when payment days change
        $('#paymentDays').on('input', function() {
            calculateDueDate();
        });
        
        // Handle ledger selection
        $('#ledgerName').on('select2:select', function(e) {
            const selectedName = e.params.data.id;
            const isNewLedger = e.params.data.newTag || false;
            
            if (ledgerData[selectedName] && !isNewLedger) {
                const ledger = ledgerData[selectedName];
                
                // Auto-fill details for existing ledger
                if (ledger.cnic) {
                    $('#ledgerCNIC').val(ledger.cnic);
                }
                if (ledger.contact) {
                    $('#ledgerContact').val(ledger.contact);
                }
                if (ledger.address) {
                    $('#ledgerAddress').val(ledger.address);
                }
                
                Swal.fire({
                    icon: 'info',
                    title: 'Ledger Details Loaded',
                    text: 'Previous ledger details have been auto-filled',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                // New ledger - clear fields
                $('#ledgerCNIC').val('');
                $('#ledgerContact').val('');
                $('#ledgerAddress').val('');
            }
        });
        
        // Form submission
        $('#dispatchForm').on('submit', function(e) {
            e.preventDefault();
            submitDispatch();
        });
    });
    
    function loadLedgers() {
        $.ajax({
            url: 'backend/getLedgers.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.ledgers.length > 0) {
                    response.ledgers.forEach(ledger => {
                        const pendingInfo = ledger.pending > 0 ? ` (Pending: Rs. ${ledger.pending.toFixed(2)})` : '';
                        const option = new Option(
                            ledger.name + pendingInfo,
                            ledger.name,
                            false,
                            false
                        );
                        $('#ledgerName').append(option);
                        
                        // Store ledger data for later use
                        ledgerData[ledger.name] = {
                            cnic: ledger.cnic,
                            contact: ledger.contact,
                            address: ledger.address,
                            pending: ledger.pending
                        };
                    });
                }
            },
            error: function() {
                console.log('Failed to load ledgers');
            }
        });
    }
    
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
        const ledgerName = $('#ledgerName').val();
        
        if (!ledgerName) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please enter or select a ledger name'
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
        var formData = {
            ledgerName: ledgerName,
            ledgerCNIC: $('#ledgerCNIC').val() || null,
            ledgerContact: $('#ledgerContact').val() || null,
            ledgerAddress: $('#ledgerAddress').val() || null,
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
            url: 'backend/saveDispatchToLedger.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Ledger dispatch created successfully!',
                        showCancelButton: true,
                        confirmButtonText: 'View Receipt',
                        cancelButtonText: 'Create New'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Open receipt in new window
                            window.open('backend/generateLedgerReceipt.php?saleID=' + response.saleID, '_blank');
                        }
                        // Reset form
                        location.reload();
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
