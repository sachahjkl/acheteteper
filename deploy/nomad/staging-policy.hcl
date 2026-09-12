namespace "staging" {
  capabilities = ["list-jobs", "parse-job", "read-job", "submit-job"]
}

host_volume "acheteteper-staging-data" {
  policy = "write"
}
