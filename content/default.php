<?php
require __DIR__ . '/../adminAuth.php';
require __DIR__ . '/../db.php';

function defaultQueryCount($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return isset($row['count']) ? intval($row['count']) : 0;
}

function defaultQuerySum($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return isset($row['total']) ? floatval($row['total']) : 0;
}

$productStatusFilter = '';
$statusColumnCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'status'");
if ($statusColumnCheck && $statusColumnCheck->num_rows > 0) {
    $productStatusFilter = ' AND status = 1';
}

// Get counts from database
$categoryCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM categories");
$colorCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM colors");
$sizeCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM sizes");
$modelCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM models");
$regionCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM regions");
$productCount = 0;
$availableStockTotal = 0;
if (isset($adminRole) && $adminRole === 'superadmin') {
    $productCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM products WHERE 1=1" . $productStatusFilter);
    $availableStockTotal = defaultQuerySum($conn, "SELECT COALESCE(SUM(available), 0) as total FROM products WHERE 1=1" . $productStatusFilter);
} else {
    $regionIDToUse = isset($regionID) ? intval($regionID) : 0;
    $productCount = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM products WHERE regionID = " . $regionIDToUse . $productStatusFilter);
    $availableStockTotal = defaultQuerySum($conn, "SELECT COALESCE(SUM(available), 0) as total FROM products WHERE regionID = " . $regionIDToUse . $productStatusFilter);
}

// DO Stats
$regionIDToUse = isset($regionID) ? intval($regionID) : 0;
$doTodayDispatch = 0;
$doTotalDispatch = 0;
$doAmountEarned = 0;
$doPending = 0;
if (!isset($adminRole) || $adminRole !== 'user') {
    if (isset($adminRole) && $adminRole === 'superadmin') {
        $doTodayDispatch = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM do_sales WHERE DATE(saleDate) = CURDATE() AND approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL");
        $doTotalDispatch = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM do_sales WHERE approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL");
        $doAmountEarned  = defaultQuerySum($conn, "SELECT COALESCE(SUM(amountPaid), 0) as total FROM do_sales WHERE approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL");
        $doPending       = defaultQuerySum($conn, "SELECT COALESCE(SUM(pendingAmount), 0) as total FROM do_sales WHERE paymentStatus IN ('pending','partial') AND approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL");
    } else {
        $doTodayDispatch = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM do_sales WHERE DATE(saleDate) = CURDATE() AND approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL AND regionID = " . $regionIDToUse);
        $doTotalDispatch = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM do_sales WHERE approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL AND regionID = " . $regionIDToUse);
        $doAmountEarned  = defaultQuerySum($conn, "SELECT COALESCE(SUM(amountPaid), 0) as total FROM do_sales WHERE approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL AND regionID = " . $regionIDToUse);
        $doPending       = defaultQuerySum($conn, "SELECT COALESCE(SUM(pendingAmount), 0) as total FROM do_sales WHERE paymentStatus IN ('pending','partial') AND approvedByAdmin IS NOT NULL AND approvedBySuperAdmin IS NOT NULL AND regionID = " . $regionIDToUse);
    }
}

// Ledger stats (section currently disabled in UI)
$ledgerTodayDispatch = 0;
$ledgerTotalDispatch = 0;
$ledgerAmountEarned = 0;
$ledgerPending = 0;
$ledgerStatsResult = $conn->query("SHOW TABLES LIKE 'ledger_sales'");
if ($ledgerStatsResult && $ledgerStatsResult->num_rows > 0) {
    $ledgerTodayDispatch = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM ledger_sales WHERE DATE(saleDate) = CURDATE()");
    $ledgerTotalDispatch = defaultQueryCount($conn, "SELECT COUNT(*) as count FROM ledger_sales");
    $ledgerAmountEarned  = defaultQuerySum($conn, "SELECT COALESCE(SUM(amountPaid), 0) as total FROM ledger_sales");
    $ledgerPending       = defaultQuerySum($conn, "SELECT COALESCE(SUM(pendingAmount), 0) as total FROM ledger_sales WHERE paymentStatus IN ('pending','partial')");
}

// Fetch all data for dropdowns
$categories = [];
$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY categoryName ASC");
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

$models = [];
$modelsResult = $conn->query("SELECT * FROM models ORDER BY modelName ASC");
if ($modelsResult) {
    while ($row = $modelsResult->fetch_assoc()) {
        $models[] = $row;
    }
}

$regions = [];
$regionsResult = $conn->query("SELECT * FROM regions ORDER BY regionName ASC");
if ($regionsResult) {
    while ($row = $regionsResult->fetch_assoc()) {
        $regions[] = $row;
    }
}
?>

<div class="container">
    <div class="packages-table">
        <h3>📊 Dashboard Overview</h3>
        <div style="padding: 20px;">
            <div style="margin-bottom: 30px;">
                <h2 style="margin-bottom: 15px; font-size: 28px;">Welcome to ARK Cool Tech Management System</h2>
                <p style="font-size: 16px; opacity: 0.9; line-height: 1.6;">
                    Manage your electronic inventory efficiently.
                </p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                
                <div onclick="loadContent('<?php echo ($adminRole === 'user') ? 'parts' : 'inventory'; ?>')" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Products</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $productCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📦</div>
                    </div>
                </div>

                <div onclick="loadContent('<?php echo ($adminRole === 'user') ? 'parts' : 'inventory'; ?>')" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Available Stock</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $availableStockTotal; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📊</div>
                    </div>
                </div>

                <div <?php if ($adminRole !== 'user'): ?>onclick="openFilterModal('model')"<?php endif; ?> style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3); <?php echo ($adminRole !== 'user') ? 'cursor: pointer;' : ''; ?> transition: transform 0.3s ease;" <?php if ($adminRole !== 'user'): ?>onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"<?php endif; ?>>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Models</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $modelCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📦</div>
                    </div>
                </div>

                <div <?php if ($adminRole !== 'user'): ?>onclick="openFilterModal('region')"<?php endif; ?> style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3); <?php echo ($adminRole !== 'user') ? 'cursor: pointer;' : ''; ?> transition: transform 0.3s ease;" <?php if ($adminRole !== 'user'): ?>onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"<?php endif; ?>>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Regions</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $regionCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">🌍</div>
                    </div>
                </div>

            </div>

            <?php if (!isset($adminRole) || $adminRole !== 'user'): ?>
            <!-- Distributing Officers Stats -->
            <div style="margin-bottom: 10px; padding: 10px 0 5px 0;">
                <h4 style="color: #667eea; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    🚚 Ware Houses
                </h4>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">

                <div onclick="loadContent('dispatchToDO')" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Today's Dispatch</div>
                            <div style="font-size: 30px; font-weight: bold;"><?php echo $doTodayDispatch; ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">📤</div>
                    </div>
                </div>

                <div onclick="loadContent('doDispatchHistory')" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(79,172,254,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Total Dispatches</div>
                            <div style="font-size: 30px; font-weight: bold;"><?php echo $doTotalDispatch; ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">📚</div>
                    </div>
                </div>

                <div onclick="loadContent('doDispatchHistory')" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(67,233,123,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Amount Earned</div>
                            <div style="font-size: 22px; font-weight: bold;">RS <?php echo number_format($doAmountEarned, 0); ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">💰</div>
                    </div>
                </div>

                <div onclick="loadContent('doPending')" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(245,87,108,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Pending Amount</div>
                            <div style="font-size: 22px; font-weight: bold;">RS <?php echo number_format($doPending, 0); ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">⏳</div>
                    </div>
                </div>

            </div>
            <?php endif; ?>

            <!-- Ledgers Stats -->
            <!-- <div style="margin-bottom: 10px; padding: 5px 0;">
                <h4 style="color: #11998e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    📒 Ledgers
                </h4>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">

                <div onclick="loadContent('dispatchToLedger')" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(17,153,142,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Today's Dispatch</div>
                            <div style="font-size: 30px; font-weight: bold;"><?php echo $ledgerTodayDispatch; ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">📤</div>
                    </div>
                </div>

                <div onclick="loadContent('ledgersDispatchHistory')" style="background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(161,140,209,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Total Dispatches</div>
                            <div style="font-size: 30px; font-weight: bold;"><?php echo $ledgerTotalDispatch; ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">📚</div>
                    </div>
                </div>

                <div onclick="loadContent('ledgersDispatchHistory')" style="background: linear-gradient(135deg, #fddb92 0%, #d1fdff 100%); padding: 22px; border-radius: 15px; color: #555; box-shadow: 0 4px 15px rgba(253,219,146,0.4); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.8; margin-bottom: 6px;">Amount Earned</div>
                            <div style="font-size: 22px; font-weight: bold;">RS <?php echo number_format($ledgerAmountEarned, 0); ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">💰</div>
                    </div>
                </div>

                <div onclick="loadContent('ledgersPending')" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 22px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(250,112,154,0.3); cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 6px;">Pending Amount</div>
                            <div style="font-size: 22px; font-weight: bold;">RS <?php echo number_format($ledgerPending, 0); ?></div>
                        </div>
                        <div style="font-size: 42px; opacity: 0.3;">⏳</div>
                    </div>
                </div>

            </div> -->
        </div>
    </div>
</div>

<!-- Filter Selection Modal -->
<div id="filterModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);">
    <div class="form-container" style="max-width: 500px; margin: 10% auto; position: relative; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px 16px 0 0;">
            <h2 style="margin: 0; color: white;" id="filterModalTitle">Select Filter</h2>
            <button onclick="closeFilterModal()" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">&times;</button>
        </div>
        <div style="padding: 30px;">
            <div class="form-group">
                <label for="filterSelect" class="form-label" style="font-size: 16px; margin-bottom: 10px; display: block;">Choose an option:</label>
                <select id="filterSelect" class="form-control" style="width: 100%;">
                    <option value="">Select...</option>
                </select>
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 30px;">
                <button type="button" class="btn btn-primary" onclick="applyFilter()" style="padding: 12px 30px;">Apply Filter</button>
                <button type="button" class="btn btn-secondary" onclick="closeFilterModal()" style="padding: 12px 30px;">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    var filterData = {
        category: <?php echo json_encode($categories); ?>,
        model: <?php echo json_encode($models); ?>,
        region: <?php echo json_encode($regions); ?>
    };
    
    var currentFilterType = null;
    
    function openFilterModal(filterType) {
        currentFilterType = filterType;
        
        // Set modal title
        const titles = {
            category: 'Select Category',
            model: 'Select Model',
            region: 'Select Region'
        };
        $('#filterModalTitle').text(titles[filterType]);
        
        // Populate dropdown
        const select = $('#filterSelect');
        select.empty();
        select.append('<option value="">Select...</option>');
        
        const data = filterData[filterType];
        const idFields = {
            category: 'categoriesID',
            model: 'modelID',
            region: 'regionID'
        };
        const nameFields = {
            category: 'categoryName',
            model: 'modelName',
            region: 'regionName'
        };
        
        data.forEach(item => {
            select.append(`<option value="${item[idFields[filterType]]}">${item[nameFields[filterType]]}</option>`);
        });
        
        // Initialize/reinitialize Select2
        select.select2({
            placeholder: 'Select an option...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#filterModal')
        });
        
        // Auto-apply filter when option is selected
        select.off('select2:select').on('select2:select', function(e) {
            const selectedValue = e.params.data.id;
            
            // Store filter in sessionStorage
            sessionStorage.setItem('inventoryFilter', JSON.stringify({
                type: currentFilterType,
                value: selectedValue
            }));
            
            // Close modal
            closeFilterModal();
            
            // Load inventory
            loadContent('inventory');
        });
        
        $('#filterModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }
    
    function closeFilterModal() {
        // Check if Select2 is initialized before destroying
        const select = $('#filterSelect');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        
        $('#filterModal').fadeOut(300);
        $('body').css('overflow', 'auto');
        currentFilterType = null;
    }
    
    function applyFilter() {
        const selectedValue = $('#filterSelect').val();
        
        if (!selectedValue) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select an option first'
            });
            return;
        }
        
        // Store filter in sessionStorage
        sessionStorage.setItem('inventoryFilter', JSON.stringify({
            type: currentFilterType,
            value: selectedValue
        }));
        
        // Close modal first
        closeFilterModal();
        
        // Then load content
        loadContent('inventory');
    }
    
    // Close modal when clicking outside
    $(document).on('click', '#filterModal', function(e) {
        if (e.target.id === 'filterModal') {
            closeFilterModal();
        }
    });
</script>
