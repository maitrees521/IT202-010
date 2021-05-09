<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$accounts = getDropDown();
?>

   <h3 class="text-center"><strong>Create Transaction</strong></h3> 
    <hr>
    <form method="POST">     
        <label>Source Account</label placeholder="0">
            <select name="s_id">
            <?php foreach($accounts as $row):?>
                <option value="<?php echo $row["id"];?>"> 
                <?php echo $row["account_number"];?>
                </option>
            <?php endforeach;?>
            </select>
        <script>
            function showTransferForm(){
                if(document.getElementById('type').value == "transfer"){
                    document.getElementById('transfer').style.display='block';
                    document.getElementById('transfer').disabled = false; 
                }else{
                    document.getElementById('transfer').style.display='none';
                    document.getElementById('transfer').disabled = true; 
                }
            }
           
        </script> 
        <div id="transfer" disabled>
            <label>Destination Account </label>
            <select name="d_id">
                <?php foreach($accounts as $row):?>
                    <option value="<?php echo $row["id"];?>">
                    <?php echo $row["account_number"];?>
                    </option>
                <?php endforeach;?>
            </select>
        </div>


        <label>Amount</label> 
        <input type="number" min="1.00" name="amount">
        <label>Action</label> 
        <select name="action" id="type" placeholder="transfer" onclick="showTransferForm()">
            <option value ="transfer">transfer</option>
            <option value ="deposit">desposit</option>
            <option value ="withdrawl">withdraw</option>
        </select>
        <label>Memo</label>
        <input type="text" name="memo">
        <input class="btn btn-primary" type ="submit" name="save" value="create"/>
    <hr> 
    </form> 


<?php
    if(isset($_POST["save"])){
        //$account_type = $_POST["account_type"];
        $source = $_POST["s_id"];  
        $destination = $_POST["d_id"];  
        $amount = $_POST["amount"];
        $action  = $_POST["action"];
        $memo = $_POST["memo"];
        $user = get_user_id();
        $db = getDB();

        $stmt=$db->prepare("SELECT id FROM Accounts WHERE account_number = '000000000000'");
        $results = $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $world_id = $r["id"];
        
        
        $stmt2=$db->prepare("SELECT balance FROM Accounts WHERE Accounts.id = :q");
        $results2 = $stmt2->execute(["q"=> $source]);
        $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $balance = $r2["balance"];

        if(!isset($memo) && empty($memo)){
            $memo = "empty";
        }
        

        switch($action){
            case "deposit":
                doBankAction($world_id, $source, ($amount * -1), $action, $memo);
            break;
            case "withdrawl":
                if($amount <= $balance){
                doBankAction($source, $world_id, ($amount * -1), $action, $memo);
                }
                elseif($amount > $balance){
                    flash("Balance Too Low");
                }
            break;
            case "transfer":
                doBankAction($source,$destination,($amount *-1), $action, $memo);
            break;
        }
          
    }
   


?>

<?php require(__DIR__ . "/partials/flash.php");
