<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash

function getState($n) {
    switch ($n) {
        case 0:
            echo "Checking";
            break;
        case 1:
            echo "Saving";
            break;
        case 2:
            echo "Loan";
            break;
        default:
            echo "Unsupported state: " . safer_echo($n);
            break;
    }
}

function getURL($path) {
    if (substr($path, 0, 1) == "/") {
        return $path;
    }
    //edit just the appended path
    return $_SERVER["CONTEXT_PREFIX"] . "/IT202-010/Project/$path";
}

function getDropDown(){
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT id, account_number FROM Accounts WHERE Accounts.user_id = :id");
    $r = $stmt->execute([
        ":id"=>$user
    ]);  

    if($r){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results; 
    }
    else{
     flash("There was a problem fetching the accounts");
    }

}



function doBankAction($acc1, $acc2, $amount, $action, $memo)
{
    $db = getDB();
    $user = get_user_id();

    $stmt2 = $db ->prepare("SELECT IFNULL(SUM(Amount),0) AS Total FROM Transactions WHERE Transactions.act_src_id = :q");
    $results2 = $stmt2->execute([":q"=> $acc1]);
    $r2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    $balanceAcc1 = $r2["Total"];

    $acc1NewBalance = $balanceAcc1 + $amount;

    $stmt3 = $db ->prepare("SELECT IFNULL(SUM(Amount),0) AS Total FROM Transactions WHERE Transactions.act_src_id = :q");
    $results3 = $stmt3->execute([":q"=> $acc2]);
    $r3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    $balanceAcc2 = $r3["Total"];
    $acc2NewBalance = $balanceAcc2 + ($amount*-1);


    $stmt = $db ->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total)
        VALUES (:s_id, :d_id, :amount, :action_type, :memo, :expected_total), (:s_id2, :d_id2, :amount2, :action_type2, :memo2, :expected_total2)" );
        //called in create then it doesn't need to be called here
            
                $r = $stmt->execute([
                    //first part
                    ":s_id" => $acc1,
                    ":d_id" => $acc2,
                    ":amount" => $amount,
                    ":action_type" => $action,
                    ":memo" => $memo,
                    ":expected_total" => $acc1NewBalance,
                    //second part
                    ":s_id2" => $acc2,
                    ":d_id2" => $acc1,
                    ":amount2" => ($amount*-1),
                    ":action_type2" => $action,
                    ":memo2" => $memo,
                    ":expected_total2" => $acc2NewBalance
                ]);
                if ($r) {
                    flash("Transaction Complete!");

                    $stmt = $db ->prepare("SELECT IFNULL(SUM(Amount),0) AS Total FROM Transactions WHERE Transactions.act_src_id = :id");
                    $r = $stmt->execute([
                            ":id" => $acc1
                    ]);
                    $results = $stmt->fetch(PDO::FETCH_ASSOC);
                    $source_total = $results["Total"]; 
                
                    if ($source_total) {
                        flash("Check 1 Successfull");
                    }
                    else {
                        $e = $stmt->errorInfo();
                        flash("Error getting source total: " . var_export($e, true));
                    }


                    $stmt = $db ->prepare("SELECT IFNULL(SUM(Amount),0) AS Total FROM Transactions WHERE Transactions.act_src_id = :id");
                    $r = $stmt->execute([
                        ":id" => $acc2
                    ]);
                    $results = $stmt->fetch(PDO::FETCH_ASSOC);
                    $destination_total = $results["Total"]; 

                    if ($destination_total) {
                        flash("Check 2 Successfull");
                    }
                    else {
                        $e = $stmt->errorInfo();
                        flash("Error getting destination total: " . var_export($e, true));
                    }

                            $stmt4=$db->prepare("UPDATE `Accounts` SET `balance` = :x WHERE id = :q");
                            $results4 = $stmt4->execute([":q"=> $acc1, ":x" => $source_total]);

                            $stmt4=$db->prepare("UPDATE `Accounts` SET `balance` = :x WHERE id = :q");
                            $results4 = $stmt4->execute([":q"=> $acc2, ":x" => $destination_total]);
                            
                        }
                        else {
                            $e = $stmt->errorInfo();
                            flash("Error creating: " . var_export($e, true));
                        }
        
}

function AnnualInterest(){
	$db = getDB();
	$numOfMonths = 1;
	$stmt = $db->prepare("SELECT id, AnnualInterest, balance FROM Accounts WHERE account_type = 'loan' AND IFNULL(nextAPY, TIMESTAMPADD(MONTH,:months,created)) <= current_timestamp"); 
	$r = $stmt->execute([":months"=>$numOfMonths]);

	if($r){
		$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if($accounts)
			{
			$stmt = $db->prepare("SELECT id FROM Accounts where account_number = '000000000000'");
			$r = $stmt->execute();
			if(!$r){
				flash(var_export($stmt->errorInfo(), true), "danger");
			}

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$world_id = $result["id"];
			foreach($accounts as $account){
				
				$apy = $account["AnnualInterest"];
				$apy /= 12;
				$balance = (float)$account["balance"];
				$change = $balance * $apy;
				do_bank_action($world_id, $account["id"], ($change * -1), "interest", "Annual Interest");
				$stmt = $db->prepare("UPDATE Accounts set balance = (SELECT IFNULL(SUM(amount_change),0) FROM Transactions WHERE act_src_id = :id), nextAPY = TIMESTAMPADD(MONTH,:months,current_timestamp) WHERE id = :id");
				$r = $stmt->execute([":id"=>$account["id"], ":months"=>$numOfMonths]);
				if(!$r){
					flash(var_export($stmt->errorInfo(), true), "danger");
				}
			}
		}
	}
	else{
		flash(var_export($stmt->errorInfo(), true), "danger");
	}
}

//end flash



?>