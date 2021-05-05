<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$accounts = getDropDown();
?>
   <h3>Create Transactions</h3>
    <form method="POST">
        <label>Source Account</label placeholder="0">
            <select name="s_id">
            <?php foreach($accounts as $row):?>
                <option value="<?php echo $row["id"];?>"> 
                <?php echo $row["account_number"];?>
                </option>
            <?php endforeach;?>
            </select>
        <label>Destination Account </label>
            <select name="d_id">
            <?php foreach($accounts as $row):?>
                <option value="<?php echo $row["id"];?>">
                <?php echo $row["account_number"];?>
                </option>
            <?php endforeach;?>
            </select>
        <label>Amount</label> 
        <input type="number" min="1.00" name="amount">
        <label>Action</label> 
        <select name="action" placeholder="withdraw">
            <option value ="deposit">desposit</option>
            <option value ="transfer">transfer</option>
            <option value ="withdrawl">withdraw</option>
        </select>

        <input type ="submit" name="save" value="create"/>
    </form>

<?php
 if(isset($_POST['type']) && isset($_POST['account1']) && isset($_POST['amount'])){
        $type = $_POST['type'];
        $memo = $_POST['memo'];
        $amount = (int)$_POST['amount'];
        switch($action){
            case "deposit":
                do_bank_action("000000000000", $_POST['account1'], ($amount * -1), $action);
            break;
            case "withdrawl":
                do_bank_action($_POST['account1'], "000000000000", ($amount * -1), $action);        
           break;
            case "transfer":
                do_bank_action($_POST['account1'], $_POST['$account2'], ($amount * -1), $action);            
          break;
           
                        $lname = $_POST['lastName'];
                        $accountnum = $_POST['account2'];
                        $stmt=$db->prepare("SELECT id FROM Accounts JOIN Users on Accounts.user_id = Users.id where Users.lname = :last_name and RIGHT(account_number,4)=:account_number LIMIT 1");
                        $stmt->execute([":account_number"=>$accountnum, ":last_name"=>$lname]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $account2 = $result['id'];
                        do_bank_action($_POST['account1'], $account2, ($amount * -1), $type, $memo);
                        break;
        }
          
    }
?>
<?php require(__DIR__ . "/partials/flash.php");