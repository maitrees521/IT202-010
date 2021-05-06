<?php require_once(__DIR__ . "/partials/nav.php");
      require_once(__DIR__ . "/lib/helpers.php");
?>

<?php
  $user = get_user_id();
  if(isset($user)){
  $results = [];
  $db = getDB();
  $stmt = $db->prepare("SELECT Accounts.user_id as UserID, Accounts.id as AccID, account_number, account_type, balance FROM Accounts WHERE Accounts.user_id = :q LIMIT 5");
  $r = $stmt->execute([":q" => $user]);
    if($r){
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
      flash("There was a problem listing your accounts"); 
    }
  }
?>

<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div><strong>Account Number:</strong></div>
                        <div><?php safer_echo($r["account_number"]); ?></div>
                    </div>
                    <div>
                        <div><strong>Account Type:</strong></div>
                        <div><?php safer_echo($r["account_type"]); ?></div>
                    </div>
                    <div>
                        <div><strong>Balance:</strong></div>
                        <div><?php safer_echo($r["balance"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="<?php echo getURL("accounts/my_transactions.php?id=" . $r["AccID"]); ?>">View Transaction History</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");