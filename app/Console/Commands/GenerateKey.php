<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA key pair';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privKey);
        $pubKey = openssl_pkey_get_details($res);
        $dir = config('app.key_path');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents("$dir/key.pub", $pubKey['key']);
        file_put_contents("$dir/key", $privKey);

        $this->info('Key: '. sha1(openssl_pkey_get_details(openssl_get_publickey($pubKey['key']))['rsa']['n']));
    }
}
