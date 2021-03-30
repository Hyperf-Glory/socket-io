<?php
declare(strict_types = 1);

namespace App\SocketIO\Parser;

use Hyperf\SocketIOServer\Parser\Packet;

class Decoder
{
    public function decode($payload) : Packet
    {
        // type
        $i     = 0;
        $type  = $payload[$i];
        $nsp   = '/';
        $query = [];
        ++$i;

        //TODO: Support attachment

        // namespace
        if (isset($payload[$i]) && $payload[$i] === '/') {
            ++$i;
            while ($payload[$i] !== ',' && $payload[$i] !== '?') {
                $nsp .= $payload[$i];
                ++$i;
            }
            if ($payload[$i] === '?') {
                ++$i;
                $query = '';
                while ($payload[$i] !== ',') {
                    $query .= $payload[$i];
                    ++$i;
                }
                $result = [];
                parse_str($query, $result);
                $query = $result;
            }
            ++$i;
        }

        // id
        $id = '';
        while (mb_strlen($payload) > $i && filter_var($payload[$i], FILTER_VALIDATE_INT) !== false) {
            $id .= $payload[$i];
            ++$i;
        }

        // data
        $data   = sprintf('%s%s%s', pack('N', strlen($payload)), $payload, "\r\n");
        $strlen = strlen($data);
        //TODO Under test
        $data   = swoole_substr_json_decode(mb_substr($data, $i), 4, $strlen - 6, true, 512, JSON_THROW_ON_ERROR) ?? [];
        //$data   = json_decode(mb_substr($payload, $i), true, 512, JSON_THROW_ON_ERROR) ?? [];
        return Packet::create([
            'type'  => $type,
            'nsp'   => $nsp,
            'id'    => $id,
            'data'  => $data,
            'query' => $query,
        ]);
    }
}
