<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * MiniProgramServiceProvider.php.
 *
 * This file is part of the wechat.
 *
 * (c) mingyoung <mingyoungcheung@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Foundation\ServiceProviders;

use EasyWeChat\MiniProgram\AccessToken;
use EasyWeChat\MiniProgram\Encryption\Encryptor;
use EasyWeChat\MiniProgram\Material\Temporary;
use EasyWeChat\MiniProgram\MiniProgram;
use EasyWeChat\MiniProgram\Notice\Notice;
use EasyWeChat\MiniProgram\QRCode\QRCode;
use EasyWeChat\MiniProgram\Server\Guard;
use EasyWeChat\MiniProgram\Sns\Sns;
use EasyWeChat\MiniProgram\Staff\Staff;
use EasyWeChat\MiniProgram\Stats\Stats;
use EasyWeChat\MiniProgram\SubscribeMessage\SubscribeMessage;
use EasyWeChat\MiniProgram\Transactions\AfterSale\AfterSale;
use EasyWeChat\MiniProgram\Transactions\AfterSale\EcAfterSale;
use EasyWeChat\MiniProgram\Transactions\Coupon\Coupon;
use EasyWeChat\MiniProgram\Transactions\Delivery\Delivery;
use EasyWeChat\MiniProgram\Transactions\Order\Order;
use EasyWeChat\MiniProgram\Transactions\Spu\Product;
use EasyWeChat\MiniProgram\Transactions\Transactions;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class MiniProgramServiceProvider.
 */
class MiniProgramServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['mini_program.access_token'] = function ($pimple) {
            return new AccessToken(
                $pimple['config']['mini_program']['app_id'],
                $pimple['config']['mini_program']['secret'],
                $pimple['cache']
            );
        };

        $pimple['mini_program.encryptor'] = function ($pimple) {
            return new Encryptor(
                $pimple['config']['mini_program']['app_id'],
                $pimple['config']['mini_program']['token'],
                $pimple['config']['mini_program']['aes_key']
            );
        };

        $pimple['mini_program.server'] = function ($pimple) {
            $server = new Guard($pimple['config']['mini_program']['token']);
            $server->debug($pimple['config']['debug']);
            $server->setEncryptor($pimple['mini_program.encryptor']);

            return $server;
        };

        $pimple['mini_program.staff'] = function ($pimple) {
            return new Staff($pimple['mini_program.access_token']);
        };

        $pimple['mini_program.notice'] = function ($pimple) {
            return new Notice($pimple['mini_program.access_token']);
        };

        $pimple['mini_program.material_temporary'] = function ($pimple) {
            return new Temporary($pimple['mini_program.access_token']);
        };

        $pimple['mini_program.stats'] = function ($pimple) {
            return new Stats(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.transaction'] = function ($pimple) {
            return new Transactions(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.product'] = function ($pimple) {
            return new Product(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };

        $pimple['mini_program.order'] = function ($pimple) {
            return new Order(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.delivery'] = function ($pimple) {
            return new Delivery(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.aftersale'] = function ($pimple) {
            return new AfterSale(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.ecAftersale'] = function ($pimple) {
            return new EcAfterSale(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.coupon'] = function ($pimple) {
            return new Coupon(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };
        $pimple['mini_program.sns'] = function ($pimple) {
            return new Sns(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };

        $pimple['mini_program.qrcode'] = function ($pimple) {
            return new QRCode(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };

        $pimple['mini_program.subscribe_message'] = function ($pimple) {
            return new SubscribeMessage(
                $pimple['mini_program.access_token'],
                $pimple['config']['mini_program']
            );
        };

        $pimple['mini_program'] = function ($pimple) {
            return new MiniProgram($pimple);
        };
    }
}
