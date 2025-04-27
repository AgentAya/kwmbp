<?php
function generateOtp() {
    return rand(100000, 999999);
}

function setUserOtp($pdo, $user_id, $otp) {
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE id = ?");
    return $stmt->execute([$otp, $expires, $user_id]);
}

function verifyUserOtp($pdo, $user_id, $inputOtp) {
    $stmt = $pdo->prepare("SELECT otp, otp_expires_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userOtpData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userOtpData) return false;
    if ($userOtpData['otp'] == $inputOtp && strtotime($userOtpData['otp_expires_at']) > time()) {
        // Clear OTP after verification
        $stmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        return true;
    }
    return false;
}
?>
