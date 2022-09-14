<?php
if ($f == "iyzipay") {
    if ($s == 'create') {
        if (!empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
            $price = Wo_Secure($_GET['amount']);
            require_once 'assets/libraries/iyzipay/samples/config.php';
            $callback_url = $wo['config']['site_url'] . "/requests.php?f=iyzipay&s=success&amount=" . $price . '&user_id=' . $wo['user']['user_id'] . '&ConversationId=' . $ConversationId;
            $request->setPrice($price);
            $request->setPaidPrice($price);
            $request->setCallbackUrl($callback_url);


            $basketItems     = array();
            $firstBasketItem = new \Iyzipay\Model\BasketItem();
            $firstBasketItem->setId("BI" . rand(11111111, 99999999));
            $firstBasketItem->setName('Top Up Wallet');
            $firstBasketItem->setCategory1('Top Up Wallet');
            $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $firstBasketItem->setPrice($price);
            $basketItems[0] = $firstBasketItem;
            $request->setBasketItems($basketItems);
            $checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, IyzipayConfig::options());
            $content                = $checkoutFormInitialize->getCheckoutFormContent();
            if (!empty($content)) {
                $data['html']   = $content;
                $data['status'] = 200;
            } else {
                $data['error']  = $wo['lang']['something_wrong'];
                $data['status'] = 400;
            }

        } else {
            $data['status'] = 400;
            $data['error']  = $wo['lang']['invalid_amount_value'];
        }
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    } elseif ($s == 'success') {
        if (!empty($_GET['ConversationId']) && !empty($_POST['token'])) {

            require_once 'assets/libraries/iyzipay/samples/config.php';
            # create request class
            $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId($_GET['ConversationId']);
            $request->setToken($_POST['token']);

            # make request
            $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, IyzipayConfig::options());

            # print result
            if ($checkoutForm->getPaymentStatus() == 'SUCCESS') {
                $amount          = Wo_Secure($_GET['amount']);
                $_GET['user_id'] = Wo_Secure($_GET['user_id']);
                $db->where('user_id', $_GET['user_id'])->update(T_USERS, array(
                    'wallet' => $db->inc($amount)
                ));
                $create_payment_log             = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENT_TRANSACTIONS . " (`userid`, `kind`, `amount`, `notes`) VALUES ('" . $_GET['user_id'] . "', 'WALLET', '" . $amount . "', 'iyzipay')");
                $_SESSION['replenished_amount'] = $amount;
                if (!empty($_COOKIE['redirect_page'])) {
                    $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                    $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                    header("Location: " . $redirect_page);
                } else {
                    header("Location: " . Wo_SeoLink('index.php?link1=wallet'));
                }
                exit();
            }
        }
        header("Location: " . Wo_SeoLink('index.php?link1=wallet'));
        exit();
    }
}
