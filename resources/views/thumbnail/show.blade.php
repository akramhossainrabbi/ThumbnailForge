@extends('layouts.app')
@section('content')
<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText">Thumbnail Request #{{ $thumbnailRequest->id }}</h1>
        </div>
        <div class="Polaris-Page__Actions">
            <span class="Polaris-Badge Polaris-Badge--status{{ ucfirst($thumbnailRequest->status) }}">
                {{ ucfirst($thumbnailRequest->status) }}
            </span>
        </div>
    </div>
    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <div style="margin-bottom:1rem;">
                    <label for="statusFilter">Filter by status:</label>
                    <select id="statusFilter">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="processed">Processed</option>
                        <option value="failed">Failed</option>
                    </select>
                    
                </div>

                <div class="Polaris-DataTable">
                    <div class="Polaris-DataTable__ScrollContainer">
                        <table class="Polaris-DataTable__Table">
                            <thead>
                                <tr>
                                    <th>Image URL</th>
                                    <th>Status</th>
                                    <th>Thumbnail</th>
                                    <th>Processed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($thumbnailRequest->jobs as $job)
                                <tr data-job-id="{{ $job->id }}">
                                    <td>{{ $job->image_url }}</td>
                                    <td class="status">
                                        <span class="Polaris-Badge Polaris-Badge--status{{ ucfirst($job->status) }}">
                                            {{ ucfirst($job->status) }}
                                        </span>
                                    </td>
                                    <td class="thumbnail">
                                        @if($job->thumbnail_url)
                                            <a href="{{ $job->thumbnail_url }}" target="_blank">View Thumbnail</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="processed-at">
                                        @if($job->processed_at)
                                            @if($job->processed_at instanceof \Illuminate\Support\Carbon)
                                                {{ $job->processed_at->format('Y-m-d H:i:s') }}
                                            @else
                                                {{ date('Y-m-d H:i:s', strtotime($job->processed_at)) }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        function subscribeToChannel() {
            if (typeof window.Echo === 'undefined') {
                return false;
            }

            window.Echo.channel('thumbnail-requests.{{ $thumbnailRequest->id }}')
                .listen('ThumbnailJobUpdated', function(e) {
                    var job = e.job;
                    var row = document.querySelector('tr[data-job-id="' + job.id + '"]');
                    if (row) {
                        row.querySelector('.status').innerHTML =
                            '<span class="Polaris-Badge Polaris-Badge--status' + (job.status.charAt(0).toUpperCase() + job.status.slice(1)) + '">' +
                            (job.status.charAt(0).toUpperCase() + job.status.slice(1)) +
                            '</span>';

                        row.querySelector('.thumbnail').innerHTML = job.thumbnail_url
                            ? '<a href="' + job.thumbnail_url + '" target="_blank">View Thumbnail</a>'
                            : '-';

                        row.querySelector('.processed-at').textContent = job.processed_at || '-';
                    }
                });

            return true;
        }

        function waitForEcho(attempts) {
            attempts = attempts || 0;
            if (subscribeToChannel()) return;
            if (attempts >= 50) {
                console.warn('Echo not available after waiting — skipping real-time subscription.');
                return;
            }
            setTimeout(function() { waitForEcho(attempts + 1); }, 100);
        }

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            waitForEcho();
        } else {
            document.addEventListener('DOMContentLoaded', function() { waitForEcho(); });
        }
        // Polling fallback when Echo is not available (or as a backup)
        var pollingInterval = 3000; // ms
        var pollHandle = null;

        function fetchJobs(status) {
            var url = '{{ route("thumbnail.jobs", $thumbnailRequest->id) }}';
            if (status) url += '?status=' + encodeURIComponent(status);
            return fetch(url, { credentials: 'same-origin' })
                .then(function(r) { return r.json(); });
        }

        function renderJobs(jobs) {
            var tbody = document.querySelector('.Polaris-DataTable__Table tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            jobs.forEach(function(job) {
                var tr = document.createElement('tr');
                tr.setAttribute('data-job-id', job.id);

                var tdUrl = document.createElement('td');
                tdUrl.textContent = job.image_url;

                var tdStatus = document.createElement('td');
                tdStatus.className = 'status';
                var statusBadge = document.createElement('span');
                statusBadge.className = 'Polaris-Badge Polaris-Badge--status' + (job.status ? (job.status.charAt(0).toUpperCase() + job.status.slice(1)) : '');
                statusBadge.textContent = job.status ? (job.status.charAt(0).toUpperCase() + job.status.slice(1)) : '';
                tdStatus.appendChild(statusBadge);

                var tdThumb = document.createElement('td');
                tdThumb.className = 'thumbnail';
                if (job.thumbnail_url) {
                    var a = document.createElement('a');
                    a.href = job.thumbnail_url;
                    a.target = '_blank';
                    a.textContent = 'View Thumbnail';
                    tdThumb.appendChild(a);
                } else {
                    tdThumb.textContent = '-';
                }

                var tdProcessed = document.createElement('td');
                tdProcessed.className = 'processed-at';
                tdProcessed.textContent = job.processed_at || '-';

                tr.appendChild(tdUrl);
                tr.appendChild(tdStatus);
                tr.appendChild(tdThumb);
                tr.appendChild(tdProcessed);

                tbody.appendChild(tr);
            });
        }

        function startPolling() {
            if (pollHandle) return;
            var status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';
            pollHandle = setInterval(function() {
                fetchJobs(status).then(renderJobs).catch(function(){});
            }, pollingInterval);
        }

        function stopPolling() {
            if (!pollHandle) return;
            clearInterval(pollHandle);
            pollHandle = null;
        }

        // If Echo isn't available after waiting, enable polling
        setTimeout(function() {
            if (typeof window.Echo === 'undefined') startPolling();
        }, 6000);

        // Filter UI
        var applyBtn = document.getElementById('applyFilter');
        var statusSelect = document.getElementById('statusFilter');

        function applyFilterAndRestart(status) {
            stopPolling();
            fetchJobs(status).then(function(jobs){ renderJobs(jobs); startPolling(); });
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                var status = statusSelect ? statusSelect.value : '';
                applyFilterAndRestart(status);
            });
        }

        // Auto-apply filter when the select changes (debounced)
        if (statusSelect) {
            var changeTimer = null;
            statusSelect.addEventListener('change', function() {
                clearTimeout(changeTimer);
                var self = this;
                changeTimer = setTimeout(function() {
                    applyFilterAndRestart(self.value);
                }, 200);
            });
        }
    })();
</script>
@endsection