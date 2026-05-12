<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
require 'authCheck.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="assets/css/toastr.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/toastr.min.js"></script>
    <script src="toast.js"></script>
    <div class="background">
        <div class="layout">
            <div class="container">
                <img class="logo" src="logo.png" alt="Logo Unload">
                <div class="heading">Sign In</div>
                <form class="form" action="loginBackend.php" method="POST">
                    <input placeholder="Username" id="username" name="username" type="text" class="input" required="" />
                    <input placeholder="Password" id="password" name="password" type="password" class="input" autocomplete="off" required="" />
                    <!-- Math CAPTCHA -->
                    <div style="margin-top: 15px; text-align: center;">
                        <label style="display: flex; align-items: center; justify-content: center; gap: 8px; color: rgb(0, 136, 255); font-weight: 600; margin-bottom: 8px; font-size: 16px;">
                            <span>Solve: <span id="mathQuestion"></span> =</span>
                            <input placeholder="?" id="mathAnswer" type="number" class="input"
                                style="width: 60px; text-align: center; padding: 8px; margin: 0;" required
                                oninput="checkMathAnswer()" />
                        </label>
                        <small id="mathError" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;">
                            ❌ Incorrect answer
                        </small>
                    </div>
                    <input type="submit" value="Sign In" id="submitBtn"
                        class="login-button"
                        disabled
                        style="opacity: 0.5; cursor: not-allowed;" />
                </form>
            </div>
        </div>
        <div class="img"></div>
    </div>
    <?php if (isset($_GET['error'])): ?>
        <script>
            const errorType = "<?php echo $_GET['error']; ?>";
            if (errorType === "invalid_password") {
                toastr.error("❌ Invalid password");
            } else if (errorType === "invalid_username") {
                toastr.error("❌ Invalid username");
            }
        </script>
    <?php endif; ?>

    <script>
        const mathEquations = [{
                question: "2 + 3",
                answer: 5
            },
            {
                question: "5 - 2",
                answer: 3
            },
            {
                question: "3 + 4",
                answer: 7
            },
            {
                question: "8 - 2",
                answer: 6
            },
            {
                question: "1 + 1",
                answer: 2
            },
            {
                question: "9 - 5",
                answer: 4
            },
            {
                question: "4 + 4",
                answer: 8
            },
            {
                question: "7 - 3",
                answer: 4
            },
            {
                question: "2 + 6",
                answer: 8
            },
            {
                question: "6 - 2",
                answer: 4
            }
        ];

        const randomEquation = mathEquations[Math.floor(Math.random() * mathEquations.length)];
        document.getElementById('mathQuestion').textContent = randomEquation.question;

        let correctAnswer = randomEquation.answer;

        function checkMathAnswer() {
            const userAnswer = parseInt(document.getElementById('mathAnswer').value);
            const submitBtn = document.getElementById('submitBtn');
            const mathError = document.getElementById('mathError');

            if (userAnswer === correctAnswer) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                mathError.style.display = 'none';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';

                if (document.getElementById('mathAnswer').value !== '') {
                    mathError.style.display = 'block';
                } else {
                    mathError.style.display = 'none';
                }
            }
        }

        // Prevent submit if wrong
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const userAnswer = parseInt(document.getElementById('mathAnswer').value);
            if (userAnswer !== correctAnswer) {
                e.preventDefault();
                alert('Please solve the math problem correctly.');
            }
        });
    </script>

</body>

</html>