<span class="alog-tag alog-tag--{{ $log->log_name }}">{{ __(\App\Models\ActivityLog::LOG_NAMES[$log->log_name] ?? $log->log_name) }}</span>
@if ($log->event && $log->event !== 'request')
    <span class="alog-ev alog-ev--{{ $log->event }}">{{ __(\App\Support\Activity\ActivityDescriber::eventLabel($log->event)) }}</span>
@endif
@if ($log->status_code)
    <span class="alog-status {{ $log->status_code >= 500 ? 'alog-status--bad' : ($log->status_code >= 400 ? 'alog-status--warn' : 'alog-status--ok') }}">{{ $log->status_code }}</span>
@endif
