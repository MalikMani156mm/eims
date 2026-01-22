<?php
require '../adminAuth.php';
require '../db.php';

// Get counts from database
$categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
$colorCount = $conn->query("SELECT COUNT(*) as count FROM colors")->fetch_assoc()['count'];
$sizeCount = $conn->query("SELECT COUNT(*) as count FROM sizes")->fetch_assoc()['count'];
$modelCount = $conn->query("SELECT COUNT(*) as count FROM models")->fetch_assoc()['count'];
$regionCount = $conn->query("SELECT COUNT(*) as count FROM regions")->fetch_assoc()['count'];
$productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
?>

<div class="container">
    <div class="packages-table">
        <h3>📊 Dashboard Overview</h3>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                
                <div onclick="loadContent('addProduct')" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Products</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $productCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📦</div>
                    </div>
                </div>

                <div onclick="loadContent('addCategory')" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Categories</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $categoryCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📁</div>
                    </div>
                </div>

                <div onclick="loadContent('addModel')" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Models</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $modelCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📦</div>
                    </div>
                </div>

                <div onclick="loadContent('addColor')" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Colors</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $colorCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">🎨</div>
                    </div>
                </div>

                <div onclick="loadContent('addSize')" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Sizes</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $sizeCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">📏</div>
                    </div>
                </div>

                <div onclick="loadContent('addRegion')" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 15px; color: white; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Regions</div>
                            <div style="font-size: 32px; font-weight: bold;"><?php echo $regionCount; ?></div>
                        </div>
                        <div style="font-size: 48px; opacity: 0.3;">🌍</div>
                    </div>
                </div>

            </div>

            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 15px; color: white; text-align: center;">
                <h2 style="margin-bottom: 15px; font-size: 28px;">Welcome to Electronic Inventory Management System</h2>
                <p style="font-size: 16px; opacity: 0.9; line-height: 1.6;">
                    Manage your electronic inventory efficiently.
                </p>
            </div>
        </div>
    </div>
</div>

