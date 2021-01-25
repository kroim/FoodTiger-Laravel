<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalWebButton;

class OrderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $order;
    protected $status;
    public function __construct($order,$status="1")
    {
        $this->order = $order;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database',OneSignalChannel::class];
    }

    public function toOneSignal($notifiable)
    {

        //$greeting=__('Your order has been accepted');
        //$line=__('We are now working on it!');

        if($this->status.""=="1"){
            //Created
            $greeting=__('There is new order');
            $line=__('You have just received an order');
        }else if($this->status.""=="3"){
            //Accepted
            $greeting=__('Your order has been accepted');
            $line=__('We are now working on it!');
        }else if($this->status.""=="5"){
            //Prepared
            $greeting=__('Your order is ready.');
            $line=__('Your order is ready for delivery. Expect us soon.');
        }else if($this->status.""=="9"){
            //Rejected
            $greeting=__('Order rejected');
            $line=__('Unfortunetly your order is rejected. There where issues with the order and we need to reject it. Pls contact us for more info.');
        }

        $url= url('/orders/'.$this->order->id);

        //Inders in the db

        return OneSignalMessage::create()
            ->subject($greeting)
            ->body($line)
            ->url($url)
            ->webButton(
                OneSignalWebButton::create('link-1')
                    ->text(__('View Order'))
                    ->icon('https://upload.wikimedia.org/wikipedia/commons/4/4f/Laravel_logo.png')
                    ->url($url)
            );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if($this->status.""=="1"){
            //Created
            $greeting=__('There is new order');
            $line=__('You have just received an order');
        }else if($this->status.""=="3"){
            //Accepted
            $greeting=__('Your order has been accepted');
            $line=__('We are now working on it!');
        }else if($this->status.""=="5"){
            //Prepared
            $greeting=__('Your order is ready.');
            $line=__('Your order is ready for delivery. Expect us soon.');
        }else if($this->status.""=="9"){
            //Rejected
            $greeting=__('Order rejected');
            $line=__('Unfortunetly your order is rejected. There where issues with the order and we need to reject it. Pls contact us for more info.');
        }


        $message=(new MailMessage)
            ->greeting($greeting)
            ->subject(__('Order notification #'.$this->order->id))
            ->line($line)
            ->action(__('View Order'), url('/orders/'.$this->order->id));


        //Add order details
            $message->line(__('Order items'));
            $message->line(__('________________'));
            foreach ($this->order->items as $key => $item) {
                $lineprice= $item->pivot->qty." X ".$item->name." ( ".money( $item->price, env('CASHIER_CURRENCY','usd'),true)." ) = ".money( $item->pivot->qty*$item->price, env('CASHIER_CURRENCY','usd'),true);
                $message->line($lineprice);
            }
            $message->line(__('________________'));
            $message->line(__('Delivery').": ".money( $this->order->delivery_price, env('CASHIER_CURRENCY','usd'),true));
            $message->line(__('Total').": ".money( $this->order->order_price, env('CASHIER_CURRENCY','usd'),true));
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable){
        if($this->status.""=="1"){
            //Created
            $greeting=__('There is new order');
            $line=__('You have just received an order');
        }else if($this->status.""=="3"){
            //Accepted
            $greeting=__('Your order has been accepted');
            $line=__('order')."#".$this->order->id." ".__('We are now working on it!');
        }else if($this->status.""=="5"){
            //Prepared
            $greeting=__('Your order is ready.');
            $line=__('Your order is ready for delivery. Expect us soon.');
        }else if($this->status.""=="9"){
            //Rejected
            $greeting=__('Order rejected');
            $line=__('Unfortunetly your order is rejected. There where issues with the order and we need to reject it. Pls contact us for more info.');
        }

        return [
            'title'=>$greeting,
            'body' =>$line
        ];
    }
}
