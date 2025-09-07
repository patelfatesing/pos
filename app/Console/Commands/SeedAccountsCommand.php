<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\AccountLedger;

class SeedAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:seed {--force : Overwrite existing data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Tally-style Chart of Accounts (groups + default ledgers)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("⚙️  Seeding Chart of Accounts...");

        // === GROUPS ===
        $groups = [
            ['Capital Account','Liability',false],
            ['Reserves & Surplus','Liability',false],
            ['Current Liabilities','Liability',false],
            ['Loans (Liability)','Liability',false],
            ['Fixed Assets','Asset',false],
            ['Investments','Asset',false],
            ['Current Assets','Asset',false],
            ['Misc. Expenses (Asset)','Asset',false],
            ['Sales Accounts','Income',true],
            ['Direct Incomes','Income',true],
            ['Indirect Incomes','Income',false],
            ['Purchase Accounts','Expense',true],
            ['Direct Expenses','Expense',true],
            ['Indirect Expenses','Expense',false],
            ['Branch / Divisions','Asset',false],
            ['Suspense A/c','Asset',false],
        ];

        $map = [];
        foreach ($groups as [$name,$nature,$gross]) {
            $map[$name] = AccountGroup::updateOrCreate(
                ['name'=>$name],
                ['nature'=>$nature,'affects_gross'=>$gross,'is_primary'=>true]
            );
        }

        // Sub-groups
        $subGroups = [
            ['Bank Accounts','Asset',$map['Current Assets']->id],
            ['Cash-in-Hand','Asset',$map['Current Assets']->id],
            ['Sundry Debtors','Asset',$map['Current Assets']->id],
            ['Sundry Creditors','Liability',$map['Current Liabilities']->id],
            ['Duties & Taxes','Liability',$map['Current Liabilities']->id],
            ['Stock-in-Hand','Asset',$map['Current Assets']->id],
            ['Provisions','Liability',$map['Current Liabilities']->id],
            ['Secured Loans','Liability',$map['Loans (Liability)']->id],
            ['Unsecured Loans','Liability',$map['Loans (Liability)']->id],
            ['Discount Rec','Income',$map['Indirect Incomes']->id],
        ];
        foreach ($subGroups as [$name,$nature,$parentId]) {
            $map[$name] = AccountGroup::updateOrCreate(
                ['name'=>$name],
                ['nature'=>$nature,'parent_id'=>$parentId]
            );
        }

        $this->info("✅ Groups seeded.");

        // === LEDGERS ===
        $ledgers = [
            ['Cash','Cash-in-Hand','Dr',0],
            ['Bank of India','Bank Accounts','Dr',0],
            ['Sales A/c','Sales Accounts','Cr',0],
            ['Purchase A/c','Purchase Accounts','Dr',0],
            ['CGST Payable','Duties & Taxes','Cr',0],
            ['SGST Payable','Duties & Taxes','Cr',0],
            ['Round Off','Indirect Expenses','Dr',0],
            ['Discount Allowed','Indirect Expenses','Dr',0],
            ['Discount Received','Discount Rec','Cr',0],
            ['Sundry Debtor A','Sundry Debtors','Dr',0],
            ['Sundry Creditor A','Sundry Creditors','Cr',0],
        ];

        foreach ($ledgers as [$name,$group,$openType,$openBal]) {
            $groupId = $map[$group]->id ?? null;
            if(!$groupId) {
                $this->warn("⚠️  Group {$group} not found, skipping ledger {$name}");
                continue;
            }

            AccountLedger::updateOrCreate(
                ['name'=>$name],
                [
                    'group_id'=>$groupId,
                    'opening_type'=>$openType,
                    'opening_balance'=>$openBal,
                ]
            );
        }

        $this->info("✅ Default ledgers seeded.");

        $this->info("🎉 Chart of Accounts setup complete!");
    }
}
