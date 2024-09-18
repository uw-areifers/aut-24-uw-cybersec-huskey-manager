<?php

// Check if the user has the necessary permissions for a specific operation on a vault
function hasPermission($operation, $vaultId) {
    $logger = $GLOBALS["logger"];
    $conn = $GLOBALS["conn"];

    // If the user is a site administrator, then they have permission so return true immediately.
    if(isset($_SESSION['isSiteAdministrator']) && $_SESSION['isSiteAdministrator'] == true ){
        return true;
    } else {
        // If the user is not an administrator, check their role and permission
        // Check if the user has permission for the specified operation and vault
        $queryUserVaultRole =  "SELECT roles.role
                                FROM vault_permissions, users, roles
                                WHERE vault_permissions.vault_id = $vaultId      
                                AND vault_permissions.role_id = roles.role_id             
                                AND vault_permissions.user_id = users.user_id
                                AND users.username = '" . $_SESSION['authenticated'] . "'";
    
        $result = $conn->query($queryUserVaultRole);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userVaultRole = $row['role'];

             // If they are the Vault Owner they get to do what they want
            if ($userVaultRole == 'Owner') {
                return true;
            }

            // Check if the user has the Editor role (return true for any operation other than delete)
            if ($userVaultRole == 'Editor' && strtoupper($operation) != 'DELETE') {
                return true;
            }

            // Check if the user has the Viewer role (return true only for READ operation)
            if ($userVaultRole  == 'Viewer' && strtoupper($operation) == 'READ') {
                return true;
            }
        }
                
        $logger->warning($_SESSION['authenticated'] ." is attempting the unauthorized action of : $operation on Vault ID : $vaultId" );

        return false;
    }
}

?>