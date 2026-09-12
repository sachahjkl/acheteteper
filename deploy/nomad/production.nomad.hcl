variable "image" {
  type        = string
  description = "Immutable GHCR image reference"

  validation {
    condition     = strlen(var.image) == 101 && substr(var.image, 0, 37) == "ghcr.io/sachahjkl/acheteteper@sha256:"
    error_message = "The image must use the Acheteteper GHCR repository and an exact SHA-256 digest."
  }
}

job "acheteteper" {
  namespace   = "production"
  datacenters = ["homelab"]
  type        = "service"

  meta {
    image = var.image
  }

  group "web" {
    count = 1

    update {
      max_parallel      = 1
      health_check      = "checks"
      min_healthy_time  = "10s"
      healthy_deadline  = "2m"
      progress_deadline = "5m"
      auto_revert       = true
    }

    restart {
      attempts = 3
      interval = "10m"
      delay    = "15s"
      mode     = "fail"
    }

    reschedule {
      attempts       = 3
      interval       = "1h"
      delay          = "30s"
      delay_function = "exponential"
      max_delay      = "5m"
      unlimited      = false
    }

    network {
      mode = "host"

      port "http" {
        to = 8000
      }
    }

    volume "data" {
      type            = "host"
      source          = "acheteteper-production-data"
      attachment_mode = "file-system"
      access_mode     = "single-node-writer"
      sticky          = true
    }

    task "backup" {
      lifecycle {
        hook    = "prestart"
        sidecar = false
      }

      driver = "docker"

      config {
        image        = var.image
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

    task "web" {
      driver = "docker"

      config {
        image        = var.image
        network_mode = "services"
        ports        = ["http"]
      }

      env {
        DB_PATH         = "/data/database.db"
        DEBUG           = "false"
        PUBLIC_URL      = "https://php.homelab.sacha.house"
        TRUSTED_PROXIES = "172.18.0.1"
        UPLOADS_PATH    = "/data/uploads"
      }

      volume_mount {
        volume      = "data"
        destination = "/data"
      }

      service {
        name     = "acheteteper-production"
        provider = "nomad"
        port     = "http"
        tags = [
          "traefik.enable=true",
          "traefik.http.routers.acheteteper-production.entrypoints=websecure",
          "traefik.http.routers.acheteteper-production.rule=Host(`php.homelab.sacha.house`)",
        ]

        check {
          name     = "HTTP health"
          type     = "http"
          path     = "/api/health"
          interval = "10s"
          timeout  = "2s"

          check_restart {
            limit           = 3
            grace           = "30s"
            ignore_warnings = false
          }
        }
      }

      resources {
        cpu    = 500
        memory = 512
      }

      logs {
        max_files     = 5
        max_file_size = 10
      }

      kill_timeout = "30s"
    }
  }
}
