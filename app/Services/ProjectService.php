<?php

namespace App\Services;

use App\Models\Priority;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['status_id'])) {
                $st = Status::find($data['status_id']);
                if ($st) {
                    $data['status'] = strtolower(str_replace(' ', '_', $st->name));
                }
            } elseif (! empty($data['status'])) {
                $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project'))
                    ->where('name', 'like', $data['status'])
                    ->first();
                if ($st) {
                    $data['status_id'] = $st->id;
                }
            }

            $data['code'] = 'PRJ-'.date('Ym').'-'.strtoupper(substr(uniqid(), -4));
            $autoCreateTicket = ! empty($data['auto_create_ticket']);
            unset($data['auto_create_ticket']);

            $project = Project::create($data);

            if ($autoCreateTicket && empty($project->ticket_id)) {
                $this->createTicketForProject($project);
            }

            return $project;
        });
    }

    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            if (! empty($data['status_id'])) {
                $st = Status::find($data['status_id']);
                if ($st) {
                    $data['status'] = strtolower(str_replace(' ', '_', $st->name));
                }
            }

            $project->update($data);

            return $project;
        });
    }

    public function addTask(Project $project, array $data): ProjectTask
    {
        return DB::transaction(function () use ($project, $data) {
            if (! empty($data['status_id'])) {
                $st = Status::find($data['status_id']);
                if ($st) {
                    $data['status'] = match (strtolower($st->name)) {
                        'to do' => 'todo',
                        'in progress' => 'in_progress',
                        'review' => 'review',
                        'done' => 'done',
                        default => strtolower(str_replace(' ', '_', $st->name))
                    };
                }
            } elseif (empty($data['status_id']) && ! empty($data['status'])) {
                $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project Task'))
                    ->where('name', 'like', str_replace('_', ' ', $data['status']))
                    ->first();
                if ($st) {
                    $data['status_id'] = $st->id;
                }
            }

            $autoCreateTicket = ! empty($data['auto_create_ticket']);
            unset($data['auto_create_ticket']);

            $task = $project->tasks()->create($data);

            if ($autoCreateTicket && empty($task->ticket_id)) {
                $this->createTicketForTask($task);
            }

            $project->syncAutoStatus();

            return $task;
        });
    }

    public function updateTask(ProjectTask $task, array $data): ProjectTask
    {
        return DB::transaction(function () use ($task, $data) {
            if (! empty($data['status_id'])) {
                $st = Status::find($data['status_id']);
                if ($st) {
                    $data['status'] = match (strtolower($st->name)) {
                        'to do' => 'todo',
                        'in progress' => 'in_progress',
                        'review' => 'review',
                        'done' => 'done',
                        default => strtolower(str_replace(' ', '_', $st->name)),
                    };
                }
            } elseif (! empty($data['status'])) {
                $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project Task'))
                    ->where('name', 'like', str_replace('_', ' ', $data['status']))
                    ->first();
                if ($st) {
                    $data['status_id'] = $st->id;
                }
            }

            $task->update($data);

            // Auto sync linked ticket if task is done
            if (($task->status === 'done' || str_contains(strtolower($task->statusRel?->name ?? ''), 'done')) && $task->ticket) {
                $closedStatus = TicketStatus::where('name', 'Closed')->first()
                    ?? TicketStatus::where('name', 'Resolved')->first()
                    ?? TicketStatus::where('is_closed', true)->first();
                if ($closedStatus) {
                    $task->ticket->update(['status_id' => $closedStatus->id]);
                }
            }

            $task->project?->syncAutoStatus();

            return $task;
        });
    }

    public function mapPriorityId(?string $priority): ?int
    {
        return match (strtolower((string) $priority)) {
            'urgent' => Priority::where('name', 'Critical')->orWhere('code', 'CRITICAL')->value('id')
                ?? Priority::first()?->id,
            'high' => Priority::where('name', 'High')->orWhere('code', 'HIGH')->value('id')
                ?? Priority::first()?->id,
            'medium' => Priority::where('name', 'Medium')->orWhere('code', 'MEDIUM')->value('id')
                ?? Priority::first()?->id,
            'low' => Priority::where('name', 'Low')->orWhere('code', 'LOW')->value('id')
                ?? Priority::first()?->id,
            default => Priority::where('name', 'Medium')->orWhere('code', 'MEDIUM')->value('id')
                ?? Priority::first()?->id,
        };
    }

    public function createTicketForProject(Project $project): Ticket
    {
        return DB::transaction(function () use ($project) {
            if ($project->ticket_id && $existing = Ticket::find($project->ticket_id)) {
                return $existing;
            }

            $ticketService = app(TicketService::class);
            $openStatus = TicketStatus::where('name', 'Open')->first() ?? TicketStatus::first();
            $reqType = TicketType::where('code', 'REQ')->orWhere('name', 'like', '%Request%')->first();

            $ticket = $ticketService->createTicket([
                'subject' => '[Project] '.$project->name,
                'problem_description' => $project->description ?: ('Project initiative: '.$project->name.' ('.$project->code.')'),
                'requester_user_id' => $project->owner_id ?: auth()->id(),
                'ticket_type_id' => $reqType?->id,
                'status_id' => $openStatus?->id,
                'priority_id' => $this->mapPriorityId($project->priority),
            ]);

            $project->update(['ticket_id' => $ticket->id]);

            return $ticket;
        });
    }

    public function createTicketForTask(ProjectTask $task): Ticket
    {
        return DB::transaction(function () use ($task) {
            if ($task->ticket_id && $existing = Ticket::find($task->ticket_id)) {
                return $existing;
            }

            $ticketService = app(TicketService::class);
            $openStatus = TicketStatus::where('name', 'Open')->first() ?? TicketStatus::first();
            $reqType = TicketType::where('code', 'REQ')->orWhere('name', 'like', '%Request%')->first();

            $project = $task->project;
            $ticket = $ticketService->createTicket([
                'subject' => '[Task] '.$task->title.($project ? ' ('.$project->code.')' : ''),
                'problem_description' => $task->description ?: ('Task: '.$task->title.' under Project '.($project?->name ?? '')),
                'requester_user_id' => $project?->owner_id ?: auth()->id(),
                'assigned_to_id' => $task->assigned_to_id,
                'asset_id' => $task->asset_id,
                'ticket_type_id' => $reqType?->id,
                'status_id' => $openStatus?->id,
                'priority_id' => $this->mapPriorityId($task->priority),
            ]);

            $task->update(['ticket_id' => $ticket->id]);

            return $ticket;
        });
    }

    public function updateTaskStatus(ProjectTask $task, array $data): array
    {
        return DB::transaction(function () use ($task, $data) {
            if (! empty($data['status_id'])) {
                $st = Status::find($data['status_id']);
                if ($st) {
                    $task->status_id = $st->id;
                    $task->status = match (strtolower($st->name)) {
                        'to do' => 'todo',
                        'in progress' => 'in_progress',
                        'review' => 'review',
                        'done' => 'done',
                        default => strtolower(str_replace(' ', '_', $st->name))
                    };
                }
            } elseif (! empty($data['status'])) {
                $task->status = $data['status'];
                $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project Task'))
                    ->where('name', 'like', str_replace('_', ' ', $data['status']))
                    ->first();
                if ($st) {
                    $task->status_id = $st->id;
                }
            }

            $task->save();

            // Auto sync linked ticket if task is done
            if (($task->status === 'done' || str_contains(strtolower($task->statusRel?->name ?? ''), 'done')) && $task->ticket) {
                $closedStatus = TicketStatus::where('name', 'Closed')->first()
                    ?? TicketStatus::where('name', 'Resolved')->first()
                    ?? TicketStatus::where('is_closed', true)->first();
                if ($closedStatus) {
                    $task->ticket->update(['status_id' => $closedStatus->id]);
                }
            }

            $project = $task->project;
            $project->syncAutoStatus();
            $stRel = $project->statusRel;

            return [
                'success' => true,
                'progress' => $project->progress_percentage,
                'status_name' => $stRel ? $stRel->name : ucfirst(str_replace('_', ' ', $project->status)),
                'status_color' => $stRel ? $stRel->color : 'info',
            ];
        });
    }
}
