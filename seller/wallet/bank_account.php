<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../../login");
    exit();
}
include '../config/.env';
include '../../includes/header.php';
$user_id = $_SESSION['user_id'];
$success = $error = "";

// 🔄 Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_code = trim($_POST['bank_name']); // actual bank code
    $bank_display_name = trim($_POST['bank_display_name']);
    $account_number = trim($_POST['account_number']);
    $account_name = trim($_POST['account_name']);

    if (empty($bank_code) || empty($bank_display_name) || empty($account_number) || empty($account_name)) {
        $error = "All fields are required.";
    } else {
        // 🔄 Create recipient on Paystack
        $paystack_key = PAYSTACK_SECRET; // Replace with live key
        $payload = [
            'type' => 'nuban',
            'name' => $account_name,
            'account_number' => $account_number,
            'bank_code' => $bank_code,
            'currency' => 'NGN'
        ];

        $ch = curl_init("https://api.paystack.co/transferrecipient");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystack_key",
            "Cache-Control: no-cache"
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $error = "Curl error: $err";
        } else {
            $result = json_decode($response, true);

            if (!$result['status']) {
                $error = "Paystack error: " . $result['message'];
            } else {
                $recipient_code = $result['data']['recipient_code'];

                // 🔍 Check if user already has bank account
                $check_stmt = $conn->prepare("SELECT id FROM bank_accounts WHERE user_id = ?");
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_stmt->store_result();

                if ($check_stmt->num_rows > 0) {
                    // 🔁 Update
                    $stmt = $conn->prepare("UPDATE bank_accounts SET bank_name=?, account_number=?, account_name=?, bank_code=?, recipient_code=? WHERE user_id=?");
                    $stmt->bind_param("sssssi", $bank_display_name, $account_number, $account_name, $bank_code, $recipient_code, $user_id);
                } else {
                    // ➕ Insert
                    $stmt = $conn->prepare("INSERT INTO bank_accounts (user_id, bank_name, account_number, account_name, bank_code, recipient_code) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssss", $user_id, $bank_display_name, $account_number, $account_name, $bank_code, $recipient_code);
                }

                if ($stmt->execute()) {
                    $success = "✅ Bank account saved & Paystack recipient created.";
                } else {
                    $error = "❌ Failed to save bank account.";
                }

                $stmt->close();
                $check_stmt->close();
            }
        }
    }
}

// 📦 Load existing bank data
$data = ['bank_name' => '', 'account_number' => '', 'account_name' => '', 'bank_code' => ''];
$q = $conn->prepare("SELECT bank_name, account_number, account_name, bank_code FROM bank_accounts WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result();
if ($res && $res->num_rows > 0) {
    $data = $res->fetch_assoc();
}
$q->close();
?>

<div class="container py-5">
    <h2 class="mb-4">🏦 Bank Account Setup</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm bg-light">
        <div class="mb-3">
            <label class="form-label">Bank Name</label>
            <select name="bank_name" id="bank_name" class="form-select" required>
                <?php if ($data['bank_code']): ?>
                    <option value="<?= htmlspecialchars($data['bank_code']) ?>" selected><?= htmlspecialchars($data['bank_name']) ?></option>
                <?php else: ?>
                    <option value="">-- Select Bank --</option>
                <?php endif; ?>
            </select>
            <input type="hidden" name="bank_display_name" id="bank_display_name" value="<?= htmlspecialchars($data['bank_name']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Account Number</label>
            <input type="text" name="account_number" maxlength="10" class="form-control" value="<?= htmlspecialchars($data['account_number']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Account Name</label>
            <input type="text" name="account_name" class="form-control" value="<?= htmlspecialchars($data['account_name']) ?>" required readonly>
        </div>

        <button type="submit" class="btn btn-primary">💾 Save Bank Info</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectBank = document.getElementById('bank_name');
    const bankDisplayInput = document.getElementById('bank_display_name');
    const accountNumberInput = document.querySelector('input[name="account_number"]');
    const accountNameInput = document.querySelector('input[name="account_name"]');

    const paystackKey = "<?= PAYSTACK_SECRET ?>";

    fetch("https://api.paystack.co/bank?country=nigeria&type=nuban", {
        headers: { Authorization: `Bearer ${paystackKey}` }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            data.data.forEach(bank => {
                const opt = document.createElement("option");
                opt.value = bank.code;
                opt.textContent = bank.name;
                opt.setAttribute("data-name", bank.name);
                if (bank.code === "<?= $data['bank_code'] ?>") {
                    opt.selected = true;
                }
                selectBank.appendChild(opt);
            });
        }
    });

    selectBank.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        bankDisplayInput.value = selectedOption.getAttribute("data-name");
    });

    accountNumberInput.addEventListener('input', function () {
        const accountNumber = this.value.trim();
        const bankCode = selectBank.value;

        if (accountNumber.length === 10 && bankCode) {
            fetch(`https://api.paystack.co/bank/resolve?account_number=${accountNumber}&bank_code=${bankCode}`, {
                headers: { Authorization: `Bearer ${paystackKey}` }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    accountNameInput.value = data.data.account_name;
                } else {
                    accountNameInput.value = '';
                    alert("❌ Unable to resolve account name.");
                }
            })
            .catch(() => {
                accountNameInput.value = '';
                alert("⚠️ Network error while resolving account.");
            });
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
