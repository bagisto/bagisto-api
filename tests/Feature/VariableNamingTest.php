<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Symfony\Component\Finder\Finder;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class VariableNamingTest extends BagistoApiTest
{
    /**
     * Variable names that hide what they hold; every variable needs a full word name.
     */
    private const BANNED = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'obj', 'rs', 'val', 'idx', 'req', 'res', 'arr', 'str', 'tmp', 'cnt', 'ctx', 'cfg', 'opt', 'opts', 'prop', 'attr', 'col', 'cols',
        'dir', 'bp', 'img', 'ds', 'dl', 'src', 'txn', 'bop', 'bo', 'av', 'cgp', 'op', 'gp', 'inv', 'ch', 'trans', 'tr', 'co', 'si',
        'rp', 'af', 'cid', 'vid', 'dst', 'fk', 'svc', 'refl', 'msg', 'fmt', 'pid', 'addr', 'cat', 'idto', 'ns',
    ];

    /**
     * No variable in the package source uses a single-letter or abbreviated name.
     */
    public function test_source_uses_full_word_variable_names(): void
    {
        $violations = [];

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in(dirname(__DIR__, 2).'/src')
            ->exclude('Resources');

        foreach ($finder as $file) {
            foreach (token_get_all(file_get_contents($file->getRealPath())) as $token) {
                if (
                    ! is_array($token)
                    || $token[0] !== T_VARIABLE
                ) {
                    continue;
                }

                if (in_array(ltrim($token[1], '$'), self::BANNED, true)) {
                    $violations[] = $file->getRelativePathname().':'.$token[2].' '.$token[1];
                }
            }
        }

        $this->assertSame([], $violations, implode(PHP_EOL, $violations));
    }
}
