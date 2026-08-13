{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-26.05";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs =
    { nixpkgs, flake-utils, ... }:
    flake-utils.lib.eachSystem
      [
        "x86_64-linux"
        "aarch64-linux"
      ]
      (
        system:
        let
          pkgs = import nixpkgs { inherit system; };
          php = pkgs.php84.withExtensions (
            { enabled, all }:
            enabled
            ++ (with all; [
              fileinfo
              pdo_sqlite
              sqlite3
            ])
          );
          src = pkgs.lib.cleanSourceWith {
            src = ./.;
            filter =
              path: type:
              let
                name = baseNameOf path;
              in
              !builtins.elem name [
                ".jj"
                ".git"
                "result"
              ];
          };
          app = pkgs.stdenvNoCC.mkDerivation {
            pname = "acheteteper";
            version = "1.0.0";
            inherit src;
            installPhase = ''
              runHook preInstall
              mkdir -p $out/share/acheteteper
              cp -r config lib src vendor $out/share/acheteteper/
              runHook postInstall
            '';
          };
          phpFpmConfig = pkgs.writeText "php-fpm.conf" ''
            [global]
            daemonize = yes
            error_log = /proc/self/fd/2
            pid = /tmp/php-fpm.pid

            [www]
            user = nobody
            group = nobody
            listen = 127.0.0.1:9000
            pm = dynamic
            pm.max_children = 8
            pm.start_servers = 2
            pm.min_spare_servers = 1
            pm.max_spare_servers = 3
            catch_workers_output = yes
            clear_env = no
          '';
          nginxConfig = pkgs.writeText "nginx.conf" ''
            user nobody nobody;
            daemon off;
              error_log /dev/stderr;
              pid /tmp/nginx.pid;

              events {}

              http {
                access_log /dev/stdout;
                client_body_temp_path /tmp/client-body;
                fastcgi_temp_path /tmp/fastcgi;
                client_max_body_size 3M;
                include ${pkgs.nginx}/conf/mime.types;
                map $http_upgrade $connection_upgrade {
                  default upgrade;
                  "" close;
                }

                server {
                  listen 8000 default_server;
                  root ${app}/share/acheteteper/src/public;
                  index index.php;

                  location / {
                    try_files $uri $uri/ /index.php?$query_string;
                  }

                  location = /realtime/socket {
                    proxy_pass http://127.0.0.1:9001;
                    proxy_http_version 1.1;
                    proxy_set_header Upgrade $http_upgrade;
                    proxy_set_header Connection $connection_upgrade;
                    proxy_read_timeout 1h;
                  }

                  location ~ \.php$ {
                    include ${pkgs.nginx}/conf/fastcgi_params;
                    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                    fastcgi_pass 127.0.0.1:9000;
                  }

                  location ~ /\. {
                    deny all;
                  }
                }
              }
          '';
          start = pkgs.writeShellApplication {
            name = "acheteteper";
            runtimeInputs = [
              php
              pkgs.coreutils
              pkgs.nginx
            ];
            text = ''
              : "''${DB_PATH:=/data/database.db}"
              : "''${UPLOADS_PATH:=/data/uploads}"
              export DB_PATH UPLOADS_PATH

              mkdir -p "$(dirname "$DB_PATH")" "$UPLOADS_PATH" /tmp/client-body /tmp/fastcgi /var/log/nginx
              chmod 1777 /tmp
              chown -R nobody:nobody "$(dirname "$DB_PATH")" "$UPLOADS_PATH" /tmp/client-body /tmp/fastcgi

              php ${app}/share/acheteteper/src/websocket.php &
              php-fpm --fpm-config ${phpFpmConfig}
              exec nginx -c ${nginxConfig}
            '';
          };
          serve = pkgs.writeShellApplication {
            name = "acheteteper-serve";
            runtimeInputs = [ php ];
            text = ''
              : "''${DB_PATH:=''${XDG_DATA_HOME:-$HOME/.local/share}/acheteteper/database.db}"
              : "''${UPLOADS_PATH:=''${XDG_DATA_HOME:-$HOME/.local/share}/acheteteper/uploads}"
              export DB_PATH UPLOADS_PATH
              mkdir -p "$(dirname "$DB_PATH")" "$UPLOADS_PATH"
              exec php -S 127.0.0.1:8000 -t ${app}/share/acheteteper/src/public ${app}/share/acheteteper/src/public/index.php
            '';
          };
          tests =
            pkgs.runCommand "acheteteper-tests"
              {
                nativeBuildInputs = [
                  php
                  pkgs.curl
                ];
              }
              ''
                cp -r ${src} source
                chmod -R u+w source
                cd source
                patchShebangs tests
                tests/all.sh
                touch $out
              '';
          lint = pkgs.runCommand "acheteteper-lint" { nativeBuildInputs = [ php ]; } ''
            cd ${src}
            find config lib src tests vendor -type f \( -name '*.php' -o -name '*.phtml' \) -print0 \
              | xargs -0 -n1 php -l
            touch $out
          '';
        in
        {
          packages = rec {
            default = app;
            dockerImage = pkgs.dockerTools.buildLayeredImage {
              name = "acheteteper";
              tag = "1.0.0";
              contents = [
                start
                pkgs.dockerTools.fakeNss
              ];
              config = {
                Cmd = [ "${start}/bin/acheteteper" ];
                Env = [
                  "DB_PATH=/data/database.db"
                  "UPLOADS_PATH=/data/uploads"
                  "DEBUG=false"
                ];
                ExposedPorts."8000/tcp" = { };
                Volumes."/data" = { };
              };
            };
          };

          apps.default = {
            type = "app";
            program = "${serve}/bin/acheteteper-serve";
          };

          checks = {
            build = app;
            inherit lint tests;
          };

          devShells.default = pkgs.mkShell {
            packages = [
              php
              pkgs.curl
              pkgs.nixfmt
              pkgs.sqlite
            ];
          };

          formatter = pkgs.nixfmt;
        }
      );
}
