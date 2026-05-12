<?php
require '../adminAuth.php';
require '../db.php';
?>

<link rel="stylesheet" href="assets/css/select2.min.css">
<link rel="stylesheet" href="assets/css/toastr.min.css">
<script src="assets/js/select2.min.js"></script>
<script src="assets/js/toastr.min.js"></script>

<style>
    .sale-form-container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        margin: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .sale-form-container h2 {
        color: #333;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }

    .sale-form-container p {
        color: #666;
        margin-bottom: 20px;
    }

    .form-card {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .card-header {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
    }

    .card-header.bg-primary {
        color: #007bff;
        border-bottom-color: #007bff;
    }

    .card-header.bg-success {
        color: #28a745;
        border-bottom-color: #28a745;
    }

    .card-header.bg-info {
        color: #17a2b8;
        border-bottom-color: #17a2b8;
    }

    .card-header.bg-warning {
        color: #ffc107;
        border-bottom-color: #ffc107;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group {
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
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control-lg {
        padding: 12px;
        font-size: 16px;
    }

    .input-group {
        display: flex;
        gap: 10px;
    }

    .input-group .form-control {
        flex: 1;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    .btn-lg {
        padding: 12px 30px;
        font-size: 16px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .table thead {
        background: #f8f9fa;
        font-weight: 600;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge.bg-primary {
        background-color: #007bff;
        color: white;
    }

    .badge.bg-secondary {
        background-color: #6c757d;
        color: white;
    }

    .badge.bg-success {
        background-color: #28a745;
        color: white;
    }

    .badge.bg-info {
        background-color: #17a2b8;
        color: white;
    }

    .badge.bg-warning {
        background-color: #ffc107;
        color: #000;
    }

    .text-center {
        text-align: center;
    }

    .text-end {
        text-align: right;
    }

    .text-muted {
        color: #6c757d;
    }

    .text-danger {
        color: #dc3545;
    }

    .text-success {
        color: #28a745;
    }

    .alert {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    #itemCount {
        background: white;
        color: #17a2b8;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: bold;
    }

    /* Receipt Styles */
    .receipt-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    .receipt-header {
        text-align: center;
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .receipt-header h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }

    .receipt-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
    }

    .receipt-info-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
    }

    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .receipt-table th,
    .receipt-table td {
        border: 1px solid #333;
        padding: 8px;
        text-align: left;
    }

    .receipt-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .receipt-summary {
        margin-top: 20px;
        border-top: 2px solid #333;
        padding-top: 15px;
    }

    .receipt-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 14px;
    }

    .receipt-summary-row.total {
        font-weight: bold;
        font-size: 16px;
        border-top: 1px solid #333;
        margin-top: 10px;
        padding-top: 10px;
    }
</style>

<div class="sale-form-container">
    <h2>💰 Add Sale</h2>
    <p>Scan items and complete the sale</p>

    <form id="saleForm">
        <!-- Customer Information -->
        <div class="form-card">
            <div class="card-header bg-primary">
                Customer Information (Optional)
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Customer Name</label>
                    <select class="form-control" id="customerName" style="width: 100%;">
                        <option value="">-- Select Existing or Type New Customer --</option>
                    </select>
                    <small class="text-muted" style="display: block; margin-top: 5px;">💡 Type to add new customer, or select from list (CNIC & Contact auto-fill)</small>
                    <input type="hidden" id="previousPending" value="0">
                    <div id="pendingAlert" style="display: none; margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; color: #856404;">
                        <strong>⚠️ Previous Pending:</strong> <span id="pendingAmount">Rs. 0.00</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>CNIC</label>
                    <input type="text" class="form-control" id="customerCnic" placeholder="xxxxx-xxxxxxx-x">
                </div>
                <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" id="customerContact" placeholder="03xxxxxxxxx">
                </div>
            </div>
        </div>

        <!-- Barcode Scanner -->
        <div class="form-card">
            <div class="card-header bg-success">
                📱 Scan Items
            </div>
            <div class="form-group">
                <label>Scan Barcode (IMEI or Product Code)</label>
                <div class="input-group">
                    <input type="text" class="form-control form-control-lg" id="barcodeInput" placeholder="Scan or enter full IMEI (14 digits) or last 4 digits" autofocus>
                    <button class="btn btn-success" type="button" id="scanBtn">🔍 Search</button>
                    <button class="btn btn-primary" type="button" id="manualAddBtn">➕ Add Item Manually</button>
                </div>
                <small class="text-muted">📱 IMEI: Auto-search on 14 digits | Enter last 4 digits + click Search | 📦 Product Code: Enter & Search | ➕ Manual: Add without scanning</small>
            </div>
        </div>

        <!-- Scanned Items -->
        <div class="form-card">
            <div class="card-header bg-info">
                Scanned Items <span id="itemCount">0 items</span>
            </div>
            <div class="table-responsive">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 20%">Item Name</th>
                            <th style="width: 17%">IMEI/Code</th>
                            <th style="width: 10%">Quantity</th>
                            <th style="width: 10%">Company Price</th>
                            <th style="width: 15%">Selling Price</th>
                            <th style="width: 8%">Discount %</th>
                            <th style="width: 10%">Total</th>
                            <th style="width: 5%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="scannedItems">
                        <tr id="noItemsRow">
                            <td colspan="10" class="text-center text-muted">No items scanned yet. Start scanning to add items.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="form-card">
            <div class="card-header bg-warning">
                💵 Payment Details
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label style="font-weight: bold;">Total Amount</label>
                    <input type="text" class="form-control form-control-lg" id="totalAmount" value="0.00" readonly style="font-weight: bold;">
                </div>
                <div class="form-group">
                    <label style="font-weight: bold;">Amount Paid by Customer</label>
                    <input type="number" step="0.01" class="form-control form-control-lg" id="amountPaid" placeholder="Enter amount paid">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <div class="alert alert-info">
                        <strong>Pending Amount:</strong> <span id="pendingAmount">0.00</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Payment Notes</label>
                    <textarea class="form-control" id="paymentNotes" rows="2" placeholder="Optional notes about payment"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-end">
            <button type="button" class="btn btn-secondary btn-lg" onclick="resetForm()" style="margin-right: 10px;">
                ❌ Cancel
            </button>
            <button type="submit" class="btn btn-primary btn-lg">
                ✅ Complete Sale
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        let scannedItems = [];
        let itemCounter = 0;
        let customerData = {};

        // Initialize Select2 for customer dropdown and load customers
        $('#customerName').select2({
            tags: true,
            placeholder: 'Type new customer name or select existing...',
            allowClear: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term + ' (New Customer)',
                    newTag: true
                };
            },
            insertTag: function(data, tag) {
                // Insert new tags at the top of the list
                data.unshift(tag);
            }
        });

        // Load customers
        loadCustomers();

        function loadCustomers() {
            $.ajax({
                url: 'ajax/getCustomers.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.customers.length > 0) {
                        response.customers.forEach(customer => {
                            const pendingInfo = customer.pending > 0 ? ` (Pending: Rs. ${customer.pending.toFixed(2)})` : '';
                            const option = new Option(
                                customer.name + pendingInfo,
                                customer.name,
                                false,
                                false
                            );
                            $('#customerName').append(option);
                            
                            // Store customer data for later use
                            customerData[customer.name] = {
                                cnic: customer.cnic,
                                contact: customer.contact,
                                pending: customer.pending
                            };
                        });
                    }
                },
                error: function() {
                    console.log('Failed to load customers');
                }
            });
        }

        // Handle customer selection
        $('#customerName').on('select2:select', function(e) {
            const selectedName = e.params.data.id;
            const isNewCustomer = e.params.data.newTag || false;
            
            if (customerData[selectedName] && !isNewCustomer) {
                const customer = customerData[selectedName];
                
                // Auto-fill CNIC and Contact for existing customer
                if (customer.cnic) {
                    $('#customerCnic').val(customer.cnic);
                }
                if (customer.contact) {
                    $('#customerContact').val(customer.contact);
                }
                
                // Show previous pending if exists
                if (customer.pending > 0) {
                    $('#previousPending').val(customer.pending);
                    $('#pendingAmount').text('Rs. ' + customer.pending.toFixed(2));
                    $('#pendingAlert').show();
                } else {
                    $('#previousPending').val(0);
                    $('#pendingAlert').hide();
                }
                
                // Show success message for auto-fill
                if (customer.cnic || customer.contact) {
                    toastr.info('Customer details auto-filled', 'Info', {timeOut: 2000});
                }
            } else {
                // New customer - clear fields and hide pending
                $('#customerCnic').val('');
                $('#customerContact').val('');
                $('#previousPending').val(0);
                $('#pendingAlert').hide();
                
                if (isNewCustomer) {
                    toastr.success('New customer - Enter CNIC and Contact', 'New Customer', {timeOut: 3000});
                }
            }
        });

        // Handle customer clear
        $('#customerName').on('select2:clear', function() {
            $('#customerCnic').val('');
            $('#customerContact').val('');
            $('#previousPending').val(0);
            $('#pendingAlert').hide();
        });

        // Auto-set amount paid to total when total changes
        $('#totalAmount').on('change', function() {
            if ($('#amountPaid').val() === '') {
                $('#amountPaid').val($(this).val());
                calculatePending();
            }
        });

        // Calculate pending amount
        $('#amountPaid').on('input', function() {
            calculatePending();
        });

        function calculatePending() {
            const total = parseFloat($('#totalAmount').val()) || 0;
            const paid = parseFloat($('#amountPaid').val()) || 0;
            const pending = total - paid;
            $('#pendingAmount').text(pending.toFixed(2));
        }

        // Handle barcode input - auto-trigger on 14 digits for IMEI
        $('#barcodeInput').on('input', function() {
            const value = $(this).val();

            // Check if input looks like IMEI (all digits)
            if (/^\d+$/.test(value)) {
                // Filter to only digits
                const cleaned = value.replace(/\D/g, '');
                $(this).val(cleaned);

                // Auto-search if 14 or more digits entered
                if (cleaned.length >= 14) {
                    searchItem();
                }
            }
        });

        $('#barcodeInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                searchItem();
            }
        });

        $('#scanBtn').on('click', function() {
            searchItem();
        });

        $('#manualAddBtn').on('click', function() {
            manualAddItem();
        });

        function searchItem() {
            const barcode = $('#barcodeInput').val().trim();

            if (!barcode) {
                Swal.fire('Error', 'Please enter IMEI or Product Code', 'error');
                return;
            }

            // Show loading
            $('#scanBtn').html('🔄 Searching...');
            $('#scanBtn').prop('disabled', true);

            $.ajax({
                url: 'ajax/getSaleItem.php',
                type: 'POST',
                data: {
                    barcode: barcode
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        addItemToTable(response.item);
                        $('#barcodeInput').val('');
                        $('#barcodeInput').focus();
                    } else {
                        // Check if item is already sold
                        if (response.already_sold) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Already Sold',
                                html: response.message,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire('Not Found', response.message, 'warning');
                        }
                        $('#barcodeInput').val('');
                        $('#barcodeInput').focus();
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to search item. Please try again.', 'error');
                },
                complete: function() {
                    $('#scanBtn').html('🔍 Search');
                    $('#scanBtn').prop('disabled', false);
                }
            });
        }

        function manualAddItem() {
            // Fetch available items
            $.ajax({
                url: 'ajax/getAvailableItems.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Manual Add Response:', response);
                    
                    if (response.success && response.items && response.items.length > 0) {
                        // Build options for select
                        let optionsHtml = '<option value="">-- Select Item --</option>';
                        response.items.forEach(item => {
                            const category = item.category;
                            const id = category === 'Mobile' ? item.mobile_id : item.accessory_id;
                            let displayName = `${item.item_name} (${category})`;
                            
                            if (category === 'Mobile') {
                                const stockInfo = item.available_imei_count > 0 ? `In Stock: ${item.available_imei_count}` : 'No Stock';
                                displayName += ` - ${stockInfo}`;
                            } else if (category === 'Accessory') {
                                const stockInfo = item.available_stock > 0 ? `Stock: ${item.available_stock}` : 'No Stock';
                                displayName += ` - ${stockInfo}`;
                            }
                            
                            displayName += ` - Rs. ${item.selling_price}`;
                            
                            optionsHtml += `<option value="${id}" data-category="${category}" data-item='${JSON.stringify(item)}'>${displayName}</option>`;
                        });

                        // Show selection dialog
                        Swal.fire({
                            title: 'Select Item',
                            html: `
                                <select id="manualItemSelect" class="form-control" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                                    ${optionsHtml}
                                </select>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Next',
                            cancelButtonText: 'Cancel',
                            width: '600px',
                            didOpen: () => {
                                // Initialize Select2 on the dropdown after dialog opens
                                $('#manualItemSelect').select2({
                                    dropdownParent: $('.swal2-container'),
                                    placeholder: 'Search items...',
                                    width: '100%'
                                });
                            },
                            preConfirm: () => {
                                const select = document.getElementById('manualItemSelect');
                                const selectedOption = select.options[select.selectedIndex];
                                
                                if (!select.value) {
                                    Swal.showValidationMessage('Please select an item');
                                    return false;
                                }
                                
                                return JSON.parse(selectedOption.getAttribute('data-item'));
                            },
                            willClose: () => {
                                // Destroy Select2 before closing dialog
                                if ($('#manualItemSelect').data('select2')) {
                                    $('#manualItemSelect').select2('destroy');
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const selectedItem = result.value;
                                showItemDetailsDialog(selectedItem);
                            }
                        });
                    } else {
                        const errorMsg = response.success === false ? response.message : 'No items found. Please add items to your inventory first.';
                        console.log('No items found:', errorMsg, 'Item count:', response.items ? response.items.length : 'undefined');
                        Swal.fire('No Items', errorMsg, 'info');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    Swal.fire('Error', 'Failed to load items. Please try again. Error: ' + error, 'error');
                }
            });
        }

        function showItemDetailsDialog(item) {
            const category = item.category;
            let maxQuantity, stockWarning = '';
            
            if (category === 'Mobile') {
                maxQuantity = item.available_imei_count || 1;
                if (item.available_imei_count === 0 || !item.available_imei_count) {
                    stockWarning = '<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin-bottom: 10px; color: #856404;"><strong>⚠️ Warning:</strong> No IMEI in stock. Sale will be recorded without IMEI tracking.</div>';
                    maxQuantity = 999; // Allow any quantity for manual entry
                }
            } else {
                maxQuantity = item.available_stock || 1;
                if (item.available_stock === 0 || !item.available_stock) {
                    stockWarning = '<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin-bottom: 10px; color: #856404;"><strong>⚠️ Warning:</strong> No stock available. Manual inventory adjustment may be required.</div>';
                    maxQuantity = 999; // Allow any quantity for manual entry
                }
            }
            
            Swal.fire({
                title: 'Item Details',
                html: `
                    <div style="text-align: left;">
                        ${stockWarning}
                        <p><strong>Item:</strong> ${item.item_name}</p>
                        <p><strong>Category:</strong> ${category}</p>
                        ${category === 'Accessory' && item.product_code ? `<p><strong>Product Code:</strong> ${item.product_code}</p>` : ''}
                        <p><strong>Company Price:</strong> Rs. ${item.company_price}</p>
                        <hr>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label><strong>Quantity:</strong></label>
                            <input type="number" id="manualQty" class="form-control" value="1" min="1" max="${maxQuantity}" style="width: 100%; padding: 8px;">
                            <small class="text-muted">${category === 'Mobile' && item.available_imei_count > 0 ? `Available IMEI: ${item.available_imei_count}` : (category === 'Accessory' && item.available_stock > 0 ? `Available: ${item.available_stock}` : 'Manual entry')}</small>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label><strong>Selling Price:</strong></label>
                            <input type="number" step="0.01" id="manualPrice" class="form-control" value="${item.selling_price}" style="width: 100%; padding: 8px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label><strong>Discount (%):</strong></label>
                            <input type="number" step="0.01" id="manualDiscount" class="form-control" value="0" min="0" max="100" style="width: 100%; padding: 8px;">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add Item',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const qty = parseInt(document.getElementById('manualQty').value);
                    const price = parseFloat(document.getElementById('manualPrice').value);
                    const discount = parseFloat(document.getElementById('manualDiscount').value);

                    if (qty < 1 || qty > maxQuantity) {
                        Swal.showValidationMessage(`Quantity must be between 1 and ${maxQuantity}`);
                        return false;
                    }

                    if (price <= 0) {
                        Swal.showValidationMessage('Price must be greater than 0');
                        return false;
                    }

                    if (discount < 0 || discount > 100) {
                        Swal.showValidationMessage('Discount must be between 0 and 100');
                        return false;
                    }

                    return {
                        quantity: qty,
                        selling_price: price,
                        discount: discount
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const details = result.value;
                    
                    // For accessories with stock, need to select purchase batch
                    if (category === 'Accessory' && item.available_stock > 0) {
                        selectAccessoryBatch(item, details);
                    } else {
                        // For mobiles or accessories without stock - direct add
                        const itemForTable = {
                            category: category,
                            item_id: category === 'Mobile' ? item.mobile_id : item.accessory_id,
                            item_name: item.item_name,
                            identifier: 'Manual Entry',
                            company_price: item.company_price,
                            selling_price: details.selling_price,
                            max_quantity: details.quantity,
                            purchase_item_id: null,
                            imei_number: null,
                            product_code: null
                        };
                        
                        addItemToTable(itemForTable, details.quantity, details.discount);
                    }
                }
            });
        }
        
        function selectAccessoryBatch(item, details) {
            // Fetch available purchase batches for this accessory
            $.ajax({
                url: 'ajax/getAccessoryPurchaseBatches.php',
                type: 'GET',
                data: { accessory_id: item.accessory_id },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.batches.length > 0) {
                        let batchOptions = '<option value="">-- Select Purchase Batch --</option>';
                        response.batches.forEach(batch => {
                            batchOptions += `<option value="${batch.purchase_item_id}" data-code="${batch.product_code}" data-stock="${batch.available_quantity}">
                                ${batch.product_code ? 'Code: ' + batch.product_code + ' - ' : ''}Available: ${batch.available_quantity}
                            </option>`;
                        });
                        
                        Swal.fire({
                            title: 'Select Purchase Batch',
                            html: `
                                <div style="text-align: left;">
                                    <p style="margin-bottom: 15px;">Select which purchase batch to use for <strong>${item.item_name}</strong>:</p>
                                    <select id="batchSelect" class="form-control" style="width: 100%; padding: 10px;">
                                        ${batchOptions}
                                    </select>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Confirm',
                            cancelButtonText: 'Cancel',
                            preConfirm: () => {
                                const select = document.getElementById('batchSelect');
                                if (!select.value) {
                                    Swal.showValidationMessage('Please select a batch');
                                    return false;
                                }
                                
                                const selectedOption = select.options[select.selectedIndex];
                                return {
                                    purchase_item_id: select.value,
                                    product_code: selectedOption.getAttribute('data-code'),
                                    max_stock: selectedOption.getAttribute('data-stock')
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const batch = result.value;
                                
                                // Validate quantity against batch stock
                                if (details.quantity > parseInt(batch.max_stock)) {
                                    Swal.fire('Error', `Only ${batch.max_stock} units available in this batch. Please reduce quantity.`, 'error');
                                    return;
                                }
                                
                                const itemForTable = {
                                    category: 'Accessory',
                                    item_id: item.accessory_id,
                                    item_name: item.item_name,
                                    identifier: batch.product_code || 'Batch #' + batch.purchase_item_id,
                                    company_price: item.company_price,
                                    selling_price: details.selling_price,
                                    max_quantity: parseInt(batch.max_stock),
                                    purchase_item_id: batch.purchase_item_id,
                                    imei_number: null,
                                    product_code: batch.product_code
                                };
                                
                                addItemToTable(itemForTable, details.quantity, details.discount);
                            }
                        });
                    } else {
                        // No batches available - manual entry
                        const itemForTable = {
                            category: 'Accessory',
                            item_id: item.accessory_id,
                            item_name: item.item_name,
                            identifier: 'Manual Entry',
                            company_price: item.company_price,
                            selling_price: details.selling_price,
                            max_quantity: details.quantity,
                            purchase_item_id: null,
                            imei_number: null,
                            product_code: null
                        };
                        
                        addItemToTable(itemForTable, details.quantity, details.discount);
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to load purchase batches.', 'error');
                }
            });
        }

        function addItemToTable(item, quantity = 1, discount = 0) {
            itemCounter++;
            $('#noItemsRow').hide();

            const subtotal = quantity * item.selling_price;
            const discountAmount = (subtotal * discount) / 100;
            const total = subtotal - discountAmount;

            const row = `
            <tr class="item-row" data-index="${itemCounter}">
                <td>${itemCounter}</td>
                <td>${item.item_name}</td>
                <td><small>${item.identifier}</small></td>
                <td>
                    <input type="number" class="form-control form-control-sm item-quantity" value="${quantity}" min="1" max="${item.max_quantity}" data-max="${item.max_quantity}" style="width: 80px;">
                    <small class="text-muted">Max: ${item.max_quantity}</small>
                </td>
                <td><input type="text" class="form-control form-control-sm" value="${item.company_price}" readonly></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-selling-price" value="${item.selling_price}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-discount" value="${discount}" min="0" max="100" style="width: 80px;"></td>
                <td><input type="text" class="form-control form-control-sm item-total" value="${total.toFixed(2)}" readonly style="font-weight: bold; width: 100px;"></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item">
                        🗑️
                    </button>
                </td>
            </tr>
        `;

            $('#scannedItems').append(row);

            // Store item data
            scannedItems.push({
                index: itemCounter,
                category: item.category,
                item_id: item.item_id,
                item_name: item.item_name,
                purchase_item_id: item.purchase_item_id || null,
                imei_number: item.imei_number || null,
                product_code: item.product_code || null,
                company_price: item.company_price,
                selling_price: item.selling_price,
                max_quantity: item.max_quantity
            });

            calculateTotal();
            updateItemCount();
        }

        // Remove item
        $(document).on('click', '.remove-item', function() {
            const row = $(this).closest('tr');
            const index = row.data('index');

            scannedItems = scannedItems.filter(item => item.index !== index);
            row.remove();

            if ($('#scannedItems tr').length === 0) {
                $('#noItemsRow').show();
            }

            calculateTotal();
            updateItemCount();
        });

        // Calculate item total when quantity, price, or discount changes
        $(document).on('input', '.item-quantity, .item-selling-price, .item-discount', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const maxQty = parseInt(row.find('.item-quantity').data('max'));
            const price = parseFloat(row.find('.item-selling-price').val()) || 0;
            const discount = parseFloat(row.find('.item-discount').val()) || 0;

            // Validate quantity
            if (quantity > maxQty) {
                row.find('.item-quantity').val(maxQty);
                Swal.fire('Invalid Quantity', `Maximum available quantity is ${maxQty}`, 'warning');
                return;
            }

            const subtotal = quantity * price;
            const discountAmount = (subtotal * discount) / 100;
            const total = subtotal - discountAmount;

            row.find('.item-total').val(total.toFixed(2));
            calculateTotal();
        });

        function calculateTotal() {
            let total = 0;
            $('.item-total').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalAmount').val(total.toFixed(2));

            // Auto-set paid amount if empty
            if ($('#amountPaid').val() === '') {
                $('#amountPaid').val(total.toFixed(2));
            }
            calculatePending();
        }

        function updateItemCount() {
            const count = $('#scannedItems tr:not(#noItemsRow)').length;
            $('#itemCount').text(`${count} item${count !== 1 ? 's' : ''}`);
        }

        // Form submission
        $('#saleForm').on('submit', function(e) {
            e.preventDefault();

            if (scannedItems.length === 0) {
                Swal.fire('Error', 'Please scan at least one item.', 'error');
                return;
            }

            const totalAmount = parseFloat($('#totalAmount').val()) || 0;
            const amountPaid = parseFloat($('#amountPaid').val()) || 0;

            if (amountPaid < 0 || amountPaid > totalAmount) {
                Swal.fire('Error', 'Invalid payment amount.', 'error');
                return;
            }

            // Gather all item data
            const items = [];
            $('#scannedItems tr:not(#noItemsRow)').each(function() {
                const row = $(this);
                const index = row.data('index');
                const itemData = scannedItems.find(item => item.index === index);

                items.push({
                    category: itemData.category,
                    item_id: itemData.item_id,
                    item_name: itemData.item_name,
                    purchase_item_id: itemData.purchase_item_id,
                    imei_number: itemData.imei_number,
                    product_code: itemData.product_code,
                    quantity: parseInt(row.find('.item-quantity').val()),
                    company_price: itemData.company_price,
                    selling_price: parseFloat(row.find('.item-selling-price').val()),
                    discount_percentage: parseFloat(row.find('.item-discount').val()) || 0,
                    total_price: parseFloat(row.find('.item-total').val())
                });
            });

            const customerName = $('#customerName').val();
            const previousPending = parseFloat($('#previousPending').val()) || 0;

            const saleData = {
                customer_name: customerName ? customerName.trim() : null,
                customer_cnic: $('#customerCnic').val().trim() || null,
                customer_contact: $('#customerContact').val().trim() || null,
                total_amount: totalAmount,
                amount_paid: amountPaid,
                payment_notes: $('#paymentNotes').val().trim() || null,
                items: items,
                previous_pending: previousPending
            };

            // Submit sale
            Swal.fire({
                title: 'Processing Sale...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'ajax/saveSale.php',
                type: 'POST',
                data: JSON.stringify(saleData),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sale Completed!',
                            html: `
                            <p><strong>Sale ID:</strong> #${response.sale_id}</p>
                            <p><strong>Total:</strong> Rs. ${totalAmount.toFixed(2)}</p>
                            <p><strong>Paid:</strong> Rs. ${amountPaid.toFixed(2)}</p>
                            ${amountPaid < totalAmount ? `<p><strong>Pending:</strong> Rs. ${(totalAmount - amountPaid).toFixed(2)}</p>` : ''}
                        `,
                            showDenyButton: true,
                            confirmButtonText: '🖨️ Print Receipt',
                            denyButtonText: '✅ New Sale',
                            confirmButtonColor: '#28a745',
                            denyButtonColor: '#007bff'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Print receipt with previous pending
                                printSaleReceipt(response.sale_id, saleData, items, previousPending);
                            }
                            resetForm();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to complete sale. Please try again.', 'error');
                }
            });
        });

        window.resetForm = function() {
            $('#saleForm')[0].reset();
            $('#scannedItems').empty();
            $('#noItemsRow').show();
            scannedItems = [];
            itemCounter = 0;
            $('#totalAmount').val('0.00');
            $('#amountPaid').val('');
            $('#pendingAmount').text('0.00');
            $('#customerName').val(null).trigger('change');
            $('#previousPending').val(0);
            $('#pendingAlert').hide();
            updateItemCount();
            $('#barcodeInput').focus();
        };

        function printSaleReceipt(saleId, saleData, items, previousPending = 0) {
            // Calculate totals
            let totalQuantity = 0;
            let totalDiscount = 0;
            let netAmount = saleData.total_amount;

            items.forEach(item => {
                totalQuantity += parseInt(item.quantity);
                const itemTotal = parseFloat(item.selling_price) * parseInt(item.quantity);
                const discountAmount = itemTotal * (parseFloat(item.discount_percentage) / 100);
                totalDiscount += discountAmount;
            });

            const receivedAmount = parseFloat(saleData.amount_paid);
            const currentPending = netAmount - receivedAmount;
            const totalPendingAmount = currentPending + parseFloat(previousPending);

            // Get current date/time
            const now = new Date();
            const saleDate = now.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) + ' ' +
                now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

            // Group items by name, price, and discount
            const groupedItems = {};
            items.forEach(item => {
                const key = `${item.item_name}_${item.selling_price}_${item.discount_percentage}`;
                if (groupedItems[key]) {
                    groupedItems[key].quantity += parseInt(item.quantity);
                    groupedItems[key].total_price += parseFloat(item.total_price);
                } else {
                    groupedItems[key] = {
                        item_name: item.item_name,
                        selling_price: parseFloat(item.selling_price),
                        discount_percentage: parseFloat(item.discount_percentage),
                        quantity: parseInt(item.quantity),
                        total_price: parseFloat(item.total_price)
                    };
                }
            });

            // Build items table HTML from grouped data
            let itemsHtml = '';
            let rowIndex = 1;
            Object.values(groupedItems).forEach(item => {
                const rate = item.selling_price;
                const qty = item.quantity;
                const discount = item.discount_percentage;
                const rateAfterDiscount = rate - (rate * discount / 100);
                const totalAmount = item.total_price;

                itemsHtml += `
                <tr>
                    <td class="text-center">${rowIndex}</td>
                    <td>${item.item_name}</td>
                    <td class="text-center">${qty}</td>
                    <td class="text-right">Rs. ${rate.toFixed(2)}</td>
                    <td class="text-center">${discount.toFixed(2)}%</td>
                    <td class="text-right">Rs. ${rateAfterDiscount.toFixed(2)}</td>
                    <td class="text-right">Rs. ${totalAmount.toFixed(2)}</td>
                </tr>
            `;
                rowIndex++;
            });

            // Create receipt HTML
            const receiptHtml = `
            <div class="receipt-container" id="printReceipt">
                <div class="receipt-header">
                    <h1>KHAN PCO & MOBILE</h1>
                    <h3 style="margin: 2px 0;">Shop # 1,2,3 St # 18 Fuji Colony</h3>
                    <h3 style="margin: 2px 0;">Bokra Road, Pirwadhai, Rawalpindi.</h3>
                    <h4 style="margin: 2px 0;">Ph: 0313-9502942</h4>
                </div>
                
                <div class="receipt-info">
                    <div class="receipt-info-item">
                        <strong>Invoice No:</strong>
                        <span>#${saleId}</span>
                    </div>
                    <div class="receipt-info-item">
                        <strong>Salesman:</strong>
                        <span><?php echo $fullName; ?></span>
                    </div>
                    <div class="receipt-info-item">
                        <strong>Customer:</strong>
                        <span>
                            ${saleData.customer_name || 'Walk-in Customer'}
                            ${previousPending > 0 ? '<br><small style="color: #dc3545; font-weight: bold;">Previous Pending: Rs. ' + previousPending.toFixed(2) + '</small>' : ''}
                        </span>
                    </div>
                    <div class="receipt-info-item">
                        <strong>Date:</strong>
                        <span>${saleDate}</span>
                    </div>
                </div>
                
                ${saleData.customer_contact || saleData.customer_cnic ? `
                <div class="receipt-info" style="margin-top: -10px;">
                    ${saleData.customer_contact ? `
                    <div class="receipt-info-item">
                        <strong>Contact:</strong>
                        <span>${saleData.customer_contact}</span>
                    </div>
                    ` : ''}
                    ${saleData.customer_cnic ? `
                    <div class="receipt-info-item">
                        <strong>CNIC:</strong>
                        <span>${saleData.customer_cnic}</span>
                    </div>
                    ` : ''}
                </div>
                ` : ''}
                
                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th class="text-center">S.No</th>
                            <th>Item Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Rate</th>
                            <th class="text-center">Discount</th>
                            <th class="text-right">RAD</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div style="padding-top: 20px; border-top: 1px solid #ccc; text-align: center;">
                        <p style="margin: 8px 0; font-size: 13px; line-height: 1.6; direction: rtl; font-weight: 500;">
                            نوٹ : موبائل فون جس کمپنی کی وارنٹی کا ہو گا وہی کمپنی اس وارنٹی کی ذمہ دار ہوگی۔
                            دکاندار وارنٹی کلیم کا پابند نہ ہو گا۔ موبائل فون واپسی پر %25 کٹوتی ہوگی۔
                        </p>
                        <p style="margin: 10px 0 5px 0; font-size: 13px; font-weight: 600; color: #333;">Software Developed By Abdul Rehman (03353376661)</p>
                        <p style="margin: 10px 0 5px 0; font-size: 13px; font-weight: 600; color: #333;">Thank you for your business!</p>
                        <p style="margin: 5px 0; font-size: 11px; color: #666; font-style: italic;">This is a computer-generated invoice.</p>
                    </div>
                    
                    <div class="receipt-summary">
                        <div class="receipt-summary-row">
                            <strong>Total Quantity:</strong>
                            <span>${totalQuantity}</span>
                        </div>
                        <div class="receipt-summary-row">
                            <strong>Net Discount:</strong>
                            <span>Rs. ${totalDiscount.toFixed(2)}</span>
                        </div>
                        <div class="receipt-summary-row total">
                            <strong>Net Amount:</strong>
                            <span>Rs. ${netAmount.toFixed(2)}</span>
                        </div>
                        <div class="receipt-summary-row">
                            <strong>Received Amount:</strong>
                            <span style="color: #28a745; font-weight: bold;">Rs. ${receivedAmount.toFixed(2)}</span>
                        </div>
                        ${previousPending > 0 ? `
                        <div class="receipt-summary-row">
                            <strong>Previous Pending:</strong>
                            <span style="color: #dc3545;">Rs. ${previousPending.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        <div class="receipt-summary-row">
                            <strong>Current Sale Pending:</strong>
                            <span style="color: #dc3545;">Rs. ${currentPending.toFixed(2)}</span>
                        </div>
                        <div class="receipt-summary-row total" style="border-top: 2px solid #333; margin-top: 5px; padding-top: 8px;">
                            <strong>Total Pending:</strong>
                            <span style="color: #dc3545; font-weight: bold;">Rs. ${totalPendingAmount.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Invoice #${saleId}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 20px;
                    }
                    .receipt-container {
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    .receipt-header {
                        text-align: center;
                        border-bottom: 2px solid #333;
                        padding-bottom: 15px;
                        margin-bottom: 20px;
                    }
                    .receipt-header h1 {
                        margin: 0;
                        font-size: 24px;
                        color: #333;
                    }
                    .receipt-info {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 10px;
                        margin-bottom: 20px;
                    }
                    .receipt-info-item {
                        display: flex;
                        justify-content: space-between;
                        padding: 5px 0;
                    }
                    .receipt-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    .receipt-table th,
                    .receipt-table td {
                        border: 1px solid #333;
                        padding: 8px;
                        text-align: left;
                    }
                    .receipt-table th:nth-child(1),
                    .receipt-table td:nth-child(1) {
                        width: 5%;
                    }
                    .receipt-table th:nth-child(2),
                    .receipt-table td:nth-child(2) {
                        width: 35%;
                    }
                    .receipt-table th:nth-child(3),
                    .receipt-table td:nth-child(3) {
                        width: 8%;
                    }
                    .receipt-table th {
                        background-color: #f0f0f0;
                        font-weight: bold;
                    }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .receipt-summary {
                        margin-top: 20px;
                        border-top: 2px solid #333;
                        padding-top: 15px;
                    }
                    .receipt-summary-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 5px 0;
                        font-size: 14px;
                    }
                    .receipt-summary-row.total {
                        font-weight: bold;
                        font-size: 16px;
                        border-top: 1px solid #333;
                        margin-top: 10px;
                        padding-top: 10px;
                    }
                    @media print {
                        @page {
                            size: A4;
                            margin: 10mm;
                        }
                    }
                </style>
            </head>
            <body>
                ${receiptHtml}
            </body>
            </html>
        `);
            printWindow.document.close();

            // Wait for content to load then print
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                // Close window after printing or canceling
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            };
        }
    });
</script>