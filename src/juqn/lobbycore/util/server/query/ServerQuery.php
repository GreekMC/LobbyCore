<?php

declare(strict_types=1);

namespace juqn\lobbycore\util\server\query;

final class ServerQuery
{
    protected const TIMEOUT = 5;

    public static function query(string $address, int $port): ?array
    {
        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

            if ($socket === null || !$socket) {
                return null;
            }
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => self::TIMEOUT, 'usec' => 0]);
            $result = socket_connect($socket, $address, $port);

            $handshake = "\xFE\xFD" . "\x09" . pack('N', rand(1, 9999999));
            socket_write($socket, $handshake, strlen($handshake));

            $readder = socket_read($socket, 65535);

            if (!$readder) {
                return null;
            }
            $token = substr($readder, 5, -1);

            if ($token) {
                $payload = "\x00\x00\x00\x00";
                $request_stat = "\xFE\xFD" . "\x00" . pack('N', rand(1, 9999999)) . pack('N', intval($token)) . $payload;
                socket_write($socket, $request_stat, strlen($request_stat));

                $readder = socket_read($socket, 65535);

                if (!$readder) {
                    return null;
                }
                $buff = substr($readder, 5);

                if ($buff) {
                    return self::parseData($buff);
                }
            }
        } catch (\Exception) {
            return null;
        } finally {
            socket_close($socket ?? null);
        }
        return null;
    }

    protected static function parseData(string $buffer): array
    {
        $data = explode("\x01", $buffer);

        if (count($data) !== 2) {
            return ['result' => 'error'];
        }
        $properties = array_slice(explode("\x00", $data[0]), 2, -2);
        $result = ['result' => 'successfully'];

        foreach (range(0, count($properties) - 1, 2) as $i) {
            $result[$properties[$i]] = $properties[$i + 1];
        }
        return $result;
    }
}
