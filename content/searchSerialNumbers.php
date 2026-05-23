<?php
require '../adminAuth.php';
require '../db.php';
?>

<div class="container">
    <div class="packages-table">
        <h3>🔍 Search Serial Numbers</h3>
        
        <!-- Search Section -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
            <div style="max-width: 600px; margin: 0 auto;">
                <label style="display: block; margin-bottom: 12px; font-weight: 600; color: white; font-size: 16px;">
                    <span>🔎 Enter Serial Number</span>
                </label>
                <div style="display: flex; gap: 12px;">
                    <input type="text" id="searchSerial" placeholder="Type serial number and press Enter..." 
                        style="flex: 1; padding: 15px 20px; border: none; border-radius: 10px; font-size: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <button onclick="searchSerialNumber()" 
                        style="padding: 15px 30px; background: white; color: #667eea; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 16px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.15)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">
                        Search
                    </button>
                    
                </div>
                <div style="display:flex; gap:12px; margin-top:12px;">
                    <input type="text" id="searchPartSerial" placeholder="Type part serial and press Enter..." 
                        style="flex:1; padding:12px 16px; border-radius:8px; border:none; box-shadow: 0 4px 12px rgba(0,0,0,0.06); font-size:14px;">
                    <button onclick="searchPartSerialNumber()" style="padding:12px 20px; background:#fff; color:#8e44ad; border:none; border-radius:8px; font-weight:700;">Search Part</button>
                </div>
                <div style="margin-top: 12px; color: white; opacity: 0.9; font-size: 14px;">
                    💡 Tip: Press Enter after typing to search quickly
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" style="display: none; text-align: center; padding: 40px;">
            <div style="display: inline-block; width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 15px; color: #666; font-size: 16px;">Searching...</p>
        </div>

        <!-- Results Container -->
        <div id="resultsContainer" style="display: none;">
            <!-- Search results will be inserted here -->
        </div>

        <!-- No Results Message -->
        <div id="noResults" style="display: none; text-align: center; padding: 60px 20px;">
            <div style="font-size: 80px; margin-bottom: 20px;">📭</div>
            <h3 style="color: #666; margin-bottom: 10px;">No Serial Number Found</h3>
            <p style="color: #999; font-size: 16px;">The serial number "<strong id="searchedSerial"></strong>" does not exist in the system.</p>
            <p style="color: #999; font-size: 14px; margin-top: 20px;">Please check the serial number and try again.</p>
        </div>

        <!-- Initial State -->
        <div id="initialState" style="text-align: center; padding: 80px 20px;">
            <div style="font-size: 100px; margin-bottom: 20px;">🔍</div>
            <h3 style="color: #667eea; margin-bottom: 10px;">Search for a Serial Number</h3>
            <p style="color: #999; font-size: 16px;">Enter a serial number above to view product details, batch information, and status.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; max-width: 800px; margin: 40px auto 0;">
                <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; border-left: 4px solid #667eea;">
                    <div style="font-size: 32px; margin-bottom: 8px;">📦</div>
                    <div style="font-size: 14px; color: #666;">Product Details</div>
                </div>
                <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; border-left: 4px solid #11998e;">
                    <div style="font-size: 32px; margin-bottom: 8px;">🏷️</div>
                    <div style="font-size: 14px; color: #666;">Batch Information</div>
                </div>
                <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; border-left: 4px solid #4caf50;">
                    <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
                    <div style="font-size: 14px; color: #666;">Status & Tracking</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    #searchSerial:focus {
        outline: none;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    }
</style>

<script>
    $(document).ready(function() {
        // Focus on search input
        $('#searchSerial').focus();
        
        // Search on Enter key
        $('#searchSerial').on('keypress', function(e) {
            if (e.which === 13) {
                searchSerialNumber();
            }
        });
        // Part serial search on Enter
        $('#searchPartSerial').on('keypress', function(e) {
            if (e.which === 13) {
                searchPartSerialNumber();
            }
        });
    });

    function searchSerialNumber() {
        const serialNumber = $('#searchSerial').val().trim();
        
        if (!serialNumber) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty Search',
                text: 'Please enter a serial number to search'
            });
            return;
        }
        
        // Hide all sections
        $('#initialState').hide();
        $('#resultsContainer').hide();
        $('#noResults').hide();
        
        // Show loading
        $('#loadingIndicator').show();
        
        // Perform AJAX search
        $.ajax({
            url: 'backend/searchSerial.php',
            type: 'GET',
            data: { serialNumber: serialNumber },
            dataType: 'json',
            success: function(response) {
                $('#loadingIndicator').hide();
                
                if (response.success && response.data) {
                    displaySerialResult(response.data);
                } else {
                    $('#searchedSerial').text(serialNumber);
                    $('#noResults').show();
                }
            },
            error: function() {
                $('#loadingIndicator').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Search Failed',
                    text: 'An error occurred while searching. Please try again.'
                });
            }
        });
    }

    function displaySerialResult(data) {
        const statusColors = {
            'available': '#4caf50',
            'issued': '#2196f3',
            'damaged': '#9e9e9e'
        };

        const statusLabels = {
            'available': 'Available',
            'issued': 'Sell',
            'damaged': 'Damaged'
        };
        
        const statusIcons = {
            'available': '✅',
            'issued': '📦',
            'damaged': '⚠️'
        };
        
        const statusColor = statusColors[data.status] || '#666';
        const statusIcon = statusIcons[data.status] || '📍';
        const statusText = statusLabels[data.status] || (data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'Unknown');
        const showSoldToInfo = data.status === 'issued';
        const soldToName = data.soldToName || 'N/A';
        const soldToType = data.soldToType || 'Customer';
        const soldAtText = data.soldAt ? new Date(data.soldAt).toLocaleString() : 'N/A';
        const soldPrice = data.soldPrice !== null && data.soldPrice !== undefined ? parseFloat(data.soldPrice).toFixed(2) : 'N/A';
        const listPrice = data.listPrice !== null && data.listPrice !== undefined ? parseFloat(data.listPrice).toFixed(2) : 'N/A';
        const discountAmount = data.discountAmount !== null && data.discountAmount !== undefined ? parseFloat(data.discountAmount).toFixed(2) : '0.00';

        const soldToHtml = showSoldToInfo ? `
                <div style="background: #f8f9ff; padding: 20px; border-radius: 12px; border-left: 4px solid #2196f3; margin-top: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                        <div>
                            <div style="font-size: 12px; color: #999; margin-bottom: 5px;">SOLD TO</div>
                            <div style="font-size: 16px; font-weight: 600; color: #333;">${soldToName}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #999; margin-bottom: 5px;">TYPE</div>
                            <div style="font-size: 16px; font-weight: 600; color: #333;">${soldToType}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #999; margin-bottom: 5px;">SOLD ON</div>
                            <div style="font-size: 16px; font-weight: 600; color: #333;">${soldAtText}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #999; margin-bottom: 5px;">LIST PRICE</div>
                            <div style="font-size: 16px; font-weight: 600; color: #333;">RS ${listPrice}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #999; margin-bottom: 5px;">DISCOUNT</div>
                            <div style="font-size: 16px; font-weight: 600; color: #333;">RS ${discountAmount}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #999; margin-bottom: 5px;">SOLD PRICE</div>
                            <div style="font-size: 16px; font-weight: 700; color: #11998e;">RS ${soldPrice}</div>
                        </div>
                    </div>
                </div>
        ` : '';
        
        const html = `
            <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <!-- Header with Serial Number -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; margin-bottom: 30px; color: white;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">SERIAL NUMBER</div>
                            <div style="font-size: 32px; font-weight: bold; letter-spacing: 2px;">${data.serialNumber}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">STATUS</div>
                            <div style="background: ${statusColor}; padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                ${statusIcon} ${statusText}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Information Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <!-- Product Details Card -->
                    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">📦 Product Details</div>
                        <div style="background: white; padding: 15px; border-radius: 8px; color: #333;">
                            <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">${data.productName || 'N/A'}</div>
                            <div style="display: grid; grid-template-columns: auto 1fr; gap: 8px; font-size: 14px;">
                                <strong>Category:</strong> <span>${data.categoryName || 'N/A'}</span>
                                <strong>Model:</strong> <span>${data.modelName || 'N/A'}</span>
                                <strong>Color:</strong> <span>${data.colorName || 'N/A'}</span>
                                <strong>Tonnage:</strong> <span>${data.sizeName || 'N/A'}</span>
                                <strong>Region:</strong> <span>${data.regionName || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Information Card -->
                    <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 12px; color: white;">
                        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">🏷️ Batch Information</div>
                        <div style="background: white; padding: 15px; border-radius: 8px; color: #333;">
                            <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px; color: #11998e;">${data.batchNumber || 'N/A'}</div>
                            <div style="display: grid; grid-template-columns: auto 1fr; gap: 8px; font-size: 14px;">
                                <strong>Cost/Unit:</strong> <span>RS ${parseFloat(data.cost || 0).toFixed(2)}</span>
                                <strong>Added On:</strong> <span>${data.createdAt ? new Date(data.createdAt).toLocaleString() : 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>

                ${soldToHtml}

                <!-- Action Buttons -->
                <!-- Issued Parts placeholder (will be filled dynamically) -->
                <div id="issuedPartsPlaceholder"></div>

                <!-- Issued Gases placeholder (will be filled dynamically) -->
                <div id="issuedGasesPlaceholder"></div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 12px; justify-content: center; margin-top: 30px;">
                    <button onclick="$('#searchSerial').val('').focus(); $('#resultsContainer').hide(); $('#initialState').show();" 
                        style="padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                        New Search
                    </button>
                    <button onclick="window.print();" 
                        style="padding: 12px 30px; background: white; color: #667eea; border: 2px solid #667eea; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                        🖨️ Print Details
                    </button>
                </div>
            </div>
        `;
        
        $('#resultsContainer').html(html).show();

        // Fetch parts issued to this product serial and render below
        $.get('backend/getPartsByIssuedSerial.php', { serialNumber: data.serialNumber }, function(partsResp) {
            if (!partsResp || !partsResp.success) return;
            const parts = partsResp.parts || [];
            if (parts.length === 0) return;

            // Group by partName
            const grouped = {};
            parts.forEach(p => {
                const name = p.partName || '(unknown)';
                if (!grouped[name]) grouped[name] = [];
                grouped[name].push(p);
            });

            let partsHtml = '<div style="background: linear-gradient(135deg, #8e44ad 0%, #6a1b9a 100%); padding: 18px; border-radius: 12px; margin-top: 20px; color: white;">';
            partsHtml += '<div style="display:flex; align-items:center; justify-content:space-between;">';
            partsHtml += '<h4 style="margin:0; font-weight:600;">🔩 Issued Parts</h4>';
            partsHtml += '</div>';
            partsHtml += '<div style="background: white; padding: 14px; border-radius: 8px; color: #333; margin-top:12px;">';

            Object.keys(grouped).forEach(partName => {
                const items = grouped[partName];
                partsHtml += `<div style="margin-bottom:18px;"><div style="font-weight:700; font-size:16px;">${partName}</div>`;

                // show serials if present, otherwise show counts
                const withSerials = items.filter(i => i.serialNumber && i.serialNumber !== '');
                const withoutSerials = items.filter(i => !i.serialNumber || i.serialNumber === '');

                // Heading for Serial Number block
                partsHtml += '<div style="margin-top:8px;">';

                if (withSerials.length) {
                    withSerials.forEach(s => {
                        const issuedAt = s.issuedDate ? s.issuedDate : '';
                        partsHtml += '<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:6px;">';
                        partsHtml += `<div style="display:flex; gap:12px; align-items:center;"><div style="font-weight:600; color:#666;">Serial Number:</div><div style="font-family:monospace; color:#222;">${s.serialNumber}</div></div>`;
                        partsHtml += `<div style="background:#4caf50; color:#fff; padding:6px 10px; border-radius:6px; font-weight:700; font-size:13px;">Issued</div>`;
                        partsHtml += '</div>';
                        partsHtml += `<div style="font-size:13px; color:#666; margin-bottom:6px;">Issued on: ${issuedAt}</div>`;
                    });
                }

                if (withoutSerials.length) {
                    const qty = withoutSerials.reduce((acc, r) => acc + (parseInt(r.quantity) || 0), 0) || withoutSerials.length;
                    const dates = withoutSerials.map(r => r.issuedDate).filter(Boolean);
                    const uniqueDates = [...new Set(dates)];

                    partsHtml += '<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:6px;">';
                    partsHtml += `<div style="display:flex; gap:12px; align-items:center;"><div style="font-weight:600; color:#666;">Serial Number:</div><div style="font-family:monospace; color:#222;">(no serial) × ${qty}</div></div>`;
                    partsHtml += `<div style="background:#4caf50; color:#fff; padding:6px 10px; border-radius:6px; font-weight:700; font-size:13px;">Issued</div>`;
                    partsHtml += '</div>';

                    if (uniqueDates.length) {
                        partsHtml += `<div style="font-size:13px; color:#666; margin-bottom:6px;">Issued on: ${uniqueDates.join(', ')}</div>`;
                    }
                }

                partsHtml += '</div>';
                partsHtml += '</div>';
            });

            partsHtml += '</div></div>';

            // Render inside the placeholder above the action buttons
            $('#issuedPartsPlaceholder').html(partsHtml);
        }, 'json').fail(function() {
            // ignore failures silently
        });

        // Fetch gases issued to this product serial
        $.get('backend/getGasByIssuedSerial.php', { serialNumber: data.serialNumber }, function(gasResp) {
            if (!gasResp || !gasResp.success) return;
            const gases = gasResp.gases || [];
            if (gases.length === 0) return;

            // Group by gasName
            const gasGrouped = {};
            gases.forEach(g => {
                const name = g.gas_name || '(unknown)';
                if (!gasGrouped[name]) gasGrouped[name] = [];
                gasGrouped[name].push(g);
            });

            let gasHtml = '<div style="background: linear-gradient(135deg, #ff9800 0%, #ff6f00 100%); padding: 18px; border-radius: 12px; margin-top: 20px; color: white;">';
            gasHtml += '<div style="display:flex; align-items:center; justify-content:space-between;">';
            gasHtml += '<h4 style="margin:0; font-weight:600;">⛽ Issued Gases</h4>';
            gasHtml += '</div>';
            gasHtml += '<div style="background: white; padding: 14px; border-radius: 8px; color: #333; margin-top:12px;">';

            Object.keys(gasGrouped).forEach(gasName => {
                const items = gasGrouped[gasName];
                gasHtml += `<div style="margin-bottom:20px;"><div style="font-weight:700; font-size:16px; color:#ff9800;">${gasName}</div>`;
                gasHtml += '<div style="margin-top:10px;">';

                items.forEach((g, idx) => {
                    const totalPrice = parseFloat(g.total_price || 0).toFixed(2);
                    const unitPrice = parseFloat(g.unit_price || 0).toFixed(2);
                    const quantity = parseFloat(g.quantity_used || 0).toFixed(2);
                    const issuedAt = g.created_at ? new Date(g.created_at).toLocaleString() : 'N/A';
                    const regionName = g.regionName || 'N/A';

                    gasHtml += '<div style="background:#f9f9f9; padding:12px; border-radius:6px; margin-bottom:10px; border-left:4px solid #ff9800;">';
                    gasHtml += `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">`;
                    gasHtml += `<div><strong style="color:#666;">Batch:</strong> ${g.batchName || 'N/A'}</div>`;
                    gasHtml += `<div style="background:#4caf50; color:#fff; padding:4px 10px; border-radius:4px; font-weight:700; font-size:12px;">Issued</div>`;
                    gasHtml += '</div>';
                    gasHtml += `<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:10px; font-size:13px;">`;
                    gasHtml += `<div><strong>Quantity:</strong> ${quantity} Units</div>`;
                    gasHtml += `<div><strong>Region:</strong> ${regionName}</div>`;
                    gasHtml += `<div><strong>Unit Price:</strong> RS ${unitPrice}</div>`;
                    gasHtml += `<div><strong>Total Price:</strong> RS ${totalPrice}</div>`;
                    gasHtml += `<div><strong>Issued on:</strong> ${issuedAt}</div>`;
                    gasHtml += '</div>';
                    gasHtml += '</div>';
                });

                gasHtml += '</div>';
                gasHtml += '</div>';
            });

            gasHtml += '</div></div>';

            // Render inside the placeholder
            $('#issuedGasesPlaceholder').html(gasHtml);
        }, 'json').fail(function() {
            // ignore failures silently
        });
    }

    function searchPartSerialNumber() {
        const partSerial = $('#searchPartSerial').val().trim();
        if (!partSerial) {
            Swal.fire('Empty', 'Please enter a part serial to search', 'warning');
            return;
        }

        // Call backend
        $.get('backend/searchPartSerial.php', { partSerial: partSerial }, function(resp) {
            $('#partSearchResult').remove();
            let html = '';
            // Ensure results container is visible
            $('#initialState').hide();
            $('#noResults').hide();
            $('#resultsContainer').show();

            if (!resp || !resp.success) {
                html = '<div id="partSearchResult" style="margin-top:12px; padding:12px; background:#fff3f3; border-radius:8px; color:#a33;">Part not found</div>';
                $('#resultsContainer').html(html);
                return;
            }

            if (resp.available) {
                const p = resp.part;
                html = `<div id="partSearchResult" style="margin-top:12px;">
                    <div style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                        <div style="font-weight:700; color:#2e7d32; font-size:16px;">Part ${p.partName} — Available</div>
                        <div style="font-size:13px; color:#444; margin-top:8px;">Batch: ${p.batchName || 'N/A'} • Region: ${p.regionName || p.regionID || 'N/A'}</div>
                    </div>
                </div>`;
                $('#resultsContainer').html(html);
                return;
            }

            // used
            if (resp.used && resp.product) {
                // show the product info using existing renderer
                displaySerialResult(resp.product);
                // also show a small note about the part
                html = `<div id="partSearchResult" style="margin-top:8px; padding:10px; background:#fff3e0; border-radius:8px; color:#8a6d3b;">This part is used in the above product (part serial: ${partSerial})</div>`;
                $('#resultsContainer').append(html);
                return;
            }

            // used but no product details
            html = `<div id="partSearchResult" style="margin-top:12px; padding:12px; background:#fff3f3; border-radius:8px; color:#a33;">Part is marked used but product information not found</div>`;
            $('#resultsContainer').html(html);
        }, 'json').fail(function() {
            Swal.fire('Error', 'Unable to contact server', 'error');
        });
    }
</script>
