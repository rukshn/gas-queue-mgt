<?php

session_start();

require_once '../common/security.php';
require_once '../common/session.php';
require_once '../common/agency.php';

if (isLoggedIn()) {
    header('Location: /agency-dashboard.php');
}

if (!isPostRequest()) {
    echo "Invalid access";
    exit(1);
}

$csrfToken = filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_STRING);
if (!isCsrfTokenValid($csrfToken)) {
    echo "CSRF attack";
    exit(1);
}

$agencyActivationOTP = filter_input(INPUT_POST, 'agencyActivationOTP', FILTER_SANITIZE_STRING);
try {
    incrementSessionValue('agency-activationAttempts');
} catch (\Exception $e) {
    echo "Error getting session values. Exiting...";
    exit(1);
}

$isActivationSuccessful = false;

try {
    $name = getSessionValue('agency-name');
    $city = getSessionValue('agency-city');
    $telephone = getSessionValue('agency-telephone');
    $agencyActivationAttempts = getSessionValue('agency-activationAttempts');
} catch (\Exception $e) {
    echo "Error getting session values. Exiting...";
    exit(1);
}

if (isAgencyActivationOTPValid($agencyActivationOTP)) {

    try {
        addAgency($name, $city, $telephone);

        setSessionValues([
            'agency-name' => null,
            'agency-city' => null,
            'agency-telephone' => null,
            'agency-activationOTP' => null,
            'agency-activationAttempts' => null,
        ]);

        $isActivationSuccessful = true;

    } catch (\Exception $e) {
        // TODO: Differentiate the error message display based on the scenario
    }
}

include_once '../templates/header.php';
include_once '../common/sessions.php';

?>
<main class="px-3 py-3 mt-5 ">
    <h1>ඒජන්සි හිමිකරු</h1>
    <p class="lead">&nbsp;</p>
    <p class="lead">
        <strong>සක්‍රීය කිරීම</strong>
    </p>
    <form class="lead" method="post" action="agency-activation-otp-verification.php">
        <?php if ($isActivationSuccessful): ?>
            <div class="row my-2 alert bg-success">
                <div class="col">
                    <?php if(getLanguage() =='si') { ?>
                        <?php echo htmlspecialchars($name, ENT_COMPAT); ?> ආයතනය
                        <?php echo htmlspecialchars($city, ENT_COMPAT); ?> නගරයේ ගෑස් සැපයුම්කරුවෙක් ලෙස ලියාපදිංචි
                        කරගන්නා ලදී.
                    <?php } else if(getLanguage() == 'en') { ?>
                        <?php echo htmlspecialchars($name, ENT_COMPAT); ?> institution was successfully registered as a gas distributor in
                        <?php echo htmlspecialchars($city, ENT_COMPAT); ?>
                    <? } ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <?php if(getLanguage() == 'si') { ?>පද්ධතියට <a href="login.php"> ඇතුළු වීමේදී (login)</a> ඔබ ඇතුලත් කළ ජංගම දුරකථන අකය
                    ඇතුළු කරන්න.
                        <?php } else if (getLanguage() == 'en') { ?>
                            When <a href="login.php">logging</a> in to the system, please use the entered mobile number
                        <?php } ?>
                </div>
            </div>
        <?php elseif ($agencyActivationAttempts >= CONFIG['app']['maxActivationAttempts']): ?>
            <div class="row my-2 alert bg-danger">
                <div class="col">කණගාටුයි! <?php echo htmlspecialchars($name, ENT_COMPAT); ?> ආයතනය
                    ලියාපදිංචි කිරීම සාර්ථක නොවුනි. කරුණාකර විනාඩි 30කින් පසු නැවත උත්සාහ කරන්න.
                </div>
            </div>
        <?php else: ?>
            <div class="row my-2 alert bg-warning text-dark">
                <div class="col">
                    <?php if(getLanguage() == 'si') { ?>
                        සක්‍රීය කිරීම සාර්ථක නොවුණි! කරුණාකර නැවත උත්සාහ කරන්න.
                    <?php } else if (getLanguate() == 'en') { ?>
                        Activation failed, please try again.
                    <?php } ?>
                </div>
            </div>

            <input type="hidden" name="csrfToken" value="<?php echo $csrfToken; ?>">
            <div class="row my-2">
                <div class="col">ඔබ විසින් <?php echo htmlspecialchars($city, ENT_COMPAT); ?> නගරයේ
                    <?php echo htmlspecialchars($name, ENT_COMPAT); ?> නැමති ව්‍යාපාරික ස්ථානය ගෑස් පෝලිම් සහයකයේ
                    ලියාපදිංචි කීරීමට ඉල්ලා ඇත. එය සනාථ කිරීමට ඔබ ඇතුලුකල දුරථනයට ලැබී ඇති SMS පණිවිඩයේ සඳහන් කේතය ඇතුළු
                    කරන්න.
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="agencyActivationOTP" class="my-1">
                        <?php if(getLanguage() == 'si') { ?>
                            කේතය
                        <?php } else if (getLanguage() == 'en') { ?>
                            Code
                        <?php } else if (getLanguage() == 'ta') { ?>

                        <?php }?>
                    </label>
                    <input type="text" maxlength="6" minlength="6" pattern="^\d{6}$" required
                           class="form-control text-center" id="agencyActivationOTP" name="agencyActivationOTP"
                           placeholder="XXXXXX" autocomplete="off">
                </div>
            </div>

            <div class="row my-4">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-success">
                        <?php if (getLanguage() == 'si') { ?>
                            සක්රිය කරන්න
                        <?php } else if (getLanguage() == 'en') { ?>
                            Activate
                        <?php } else if (getLanguage() == 'ta') { ?>

                        <?php } ?>
                    </button>
                </div>
                <div class="col-sm-12">
                    <a href="/" class="btn btn-link">
                        <?php if(getLanguage() == 'si') { ?>
                            ආපසු
                        <?php } else if (getLanguage() == 'en') { ?>
                            Back
                        <?php } else if (getLanguage() == 'ta') { ?>

                        <?php } ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </form>

</main>

<?php include_once '../templates/footer.php' ?>

