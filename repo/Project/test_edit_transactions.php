<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    if($transaction <= 0){
        $transaction = null; 
    }
    $amount = $_POST("amount");
    $user = get_user_id();
    $db = getDB();

    if (isset($id)) { //balance and trasnaction type
        $stmt = $db->prepare("UPDATE Transactions set amount where id=:id"); //check proper ID
        $r = $stmt->execute([
            ":amount" => $amount,
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM TRANSACTIONS where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>
    <h3>Edit Transaction</h3>
    <form method="POST">
        
        <label> Amount Change </label> 
        <input type="number" min="10.00" name="amount" value="<?php echo $result["amount"];?>" />
        <input type="submit" name="save" value="Update"/>

    </form>


 <?php require(__DIR__ . "/partials/flash.php");