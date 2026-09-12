namespace "staging" {
  capabilities = ["list-jobs", "read-job"]
}

namespace "production" {
  capabilities = ["list-jobs", "parse-job", "read-job", "submit-job"]
}

host_volume "acheteteper-production-data" {
  policy = "write"
}
