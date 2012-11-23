#!/bin/sh

clear

openssl genrsa -out private.pem 2048

openssl rsa -in private.pem -out public.pem -pubout

echo
echo
echo "~~~~~~~~~~[Private Key]~~~~~~~~~~"
echo
cat private.pem | tr "\n" "|"
echo
echo

echo "~~~~~~~~~~[Public Key]~~~~~~~~~~"
echo
cat public.pem | tr "\n" "|"
echo
echo
echo

rm -f private.pem public.pem