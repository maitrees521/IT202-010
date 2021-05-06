<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $tranID = $_GET["id"];
}
?>

<?php
$result = [];
if (isset($tranID)) {
    $db = getDB();
    $user = get_user_id();
    $stmt = $db->prepare("SELECT Transactions.id as tranID, act_src_id, act_dest_id, amount, action_type FROM Transactions WHERE Transactions.id = :q");
    $r = $stmt->execute([":q" => $tranID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
    
}
?>


<h3>View Transaction</h3>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
        </div>
        <div class="card-body">
            <div>
                <p><b>Information</b></p> <!-- match with SELECT ^^^^^^ -->
                <div>Amount:<?php safer_echo($result["amount"]); ?></div>
                <div>Action: Type <?php safer_echo($result["action_type"]); ?> </div>
                <div>Tran ID: <?php safer_echo($result["tranID"]); ?> </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up for id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");