    <?php


    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up(): void
        {
            Schema::create('stock_request_approves', function (Blueprint $table) {
                $table->id();

                $table->foreignId('stock_request_id')->constrained('stock_requests')->onDelete('cascade');
                $table->foreignId('stock_request_item_id')->constrained('stock_request_items')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('source_store_id')->constrained('branches');
                $table->foreignId('destination_store_id')->constrained('branches');

                $table->unsignedInteger('approved_quantity')->default(0);
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();

                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('stock_request_approves');
        }
    };
