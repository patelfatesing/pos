<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\StockRequest;

class Notification extends Component
{
    public $notifications = [];
    public $readNotificationsCount = '';
    public $showPopup = false;

    public $selectedNotificationId = null;
    public $selectedNotificationData = null;
    public $notificationType = null;
    public $selectedNotificationDataId = null;

    public function togglePopup()
    {
        $this->showPopup = !$this->showPopup;
    }

    public function viewNotificationDetail($notificationId, $type,$red_id,$id)
    {
        $this->selectedNotificationId = $notificationId;
        $this->notificationType = $type;

        updateUnreadNotificationsById($id);

        switch ($type) {
            case 'low_stock':
                $this->selectedNotificationData = DB::table('products')
                ->select(
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.sku',
                    'products.reorder_level',
                    DB::raw('IFNULL(SUM(inventories.quantity), 0) as total_stock')
                )
                ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('products.is_deleted', 'no')
                ->where('products.is_active', 'yes')
                ->groupBy(
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.sku',
                    'products.reorder_level'
                )
                ->havingRaw('total_stock <= products.reorder_level')
                ->get();
                // dd($this->selectedNotificationData);
                break;

            case 'approved_stock':
                // Example dummy logic (adjust to your app logic)
                // $id  = $request->id;
                $this->selectedNotificationData = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($red_id);
                break;

            case 'price_change':
                $this->selectedNotificationData = DB::table('product_price_logs as ppl')
                    ->join('products as p', 'ppl.product_id', '=', 'p.id')
                    ->orderBy('ppl.changed_at', 'desc')
                    ->select('p.name', 'ppl.old_price', 'ppl.new_price', 'ppl.changed_at')
                    ->take(10)
                    ->get();
                break;

            case 'expire_product':
                $this->selectedNotificationData = DB::table('inventories as i')
                    ->join('products as p', 'i.product_id', '=', 'p.id')
                    ->where('i.expiry_date', '<', Carbon::today())
                    ->select(
                        'i.id as inventory_id',
                        'i.product_id',
                        'p.name as product_name',
                        'p.brand',
                        'i.batch_no',
                        'i.expiry_date',
                        'i.quantity',
                        'p.sku',
                        'p.barcode',
                        'i.store_id',
                        'i.location_id'
                    )
                    ->orderBy('i.expiry_date')
                    ->get();
                break;
                case 'transfer_stock':
                    $this->selectedNotificationData = DB::table('stock_transfers as i')
                        ->join('products as p', 'i.product_id', '=', 'p.id')
                        ->where('i.transfer_number', $red_id)
                        ->select(
                            'i.id as id',
                            'i.product_id',
                            'p.name as product_name',
                            'p.brand',
                            'i.transfer_number',
                            'i.quantity',
                            'p.sku',
                            'p.barcode',
                            'i.to_branch_id'
                        )
                        ->orderBy('i.created_at')
                        ->get();
                        // dd($this->selectedNotificationData);
                    break;    
            default:
                $this->selectedNotificationData = collect([
                    'message' => 'Notification not found.',
                    'time' => now()->toDateTimeString(),
                ]);
                break;
        }
    }

    public function closeNotificationDetail()
    {
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->selectedNotificationDataId = null;
        $this->notificationType = null;
    }

    public function render()
    {
        $branchId = auth()->user()->userinfo->branch->id ?? null;

        $getNotification = getNotificationsByNotifyTo(auth()->id(), $branchId, 5);

        $count = getUnreadNotificationsByNotifyTo(auth()->id(), $branchId, 5);

        $notiAry = [];

        foreach ($getNotification as $key => $noti) {

            if(!is_null($noti->details)){
                $data = json_decode($noti->details);
                if (isset($data->id)) {
                    $noti->details = $data->id;
                } else {
                    $noti->details = null;
                }
            } else {
                $noti->details = null;
            }

            $notiAry[$key] = [
                'req_id' => $noti->details,
                'message' => $noti->content,
                'notify_to' => $noti->notify_to,
                'type' => $noti->type,
                'status' => $noti->status,
                'time' => $noti->created_at->diffForHumans(),
                'id' => $noti->id
            ];
        }

        $this->notifications = $notiAry;
        $this->readNotificationsCount = $count;

        return view('livewire.notification');
    }
}
