    task "backup" {
      lifecycle {
        hook    = "prestart"
        sidecar = false
      }

      driver = "docker"

      config {
        image        = "ghcr.io/sachahjkl/acheteteper@sha256:0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef"
        command      = "/bin/sh"
        args         = ["-ec", "test -s /data/database.db; mkdir -p /data/backups /tmp/backup; sqlite3 /data/database.db \".backup '/tmp/backup/database.db'\"; cp -a /data/uploads /tmp/backup/uploads; tar -czf /data/backups/pre-deploy-$NOMAD_ALLOC_ID.tar.gz -C /tmp/backup database.db uploads"]
        network_mode = "services"
      }

      volume_mount {
        volume      = "data"
        destination = "/data"
      }

      resources {
        cpu    = 100
        memory = 128
      }
    }
