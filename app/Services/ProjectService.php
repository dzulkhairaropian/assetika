<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['status_id']) && ! empty($data['status'])) {
                $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project'))
                    ->where('name', 'like', $data['status'])
                    ->first();
                if ($st) {
                    $data['status_id'] = $st->id;
                }
            }

            $data['code'] = 'PRJ-'.date('Ym').'-'.strtoupper(substr(uniqid(), -4));

            return Project::create($data);
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

            $task = $project->tasks()->create($data);
            $project->syncAutoStatus();

            return $task;
        });
    }

    public function updateTaskStatus(ProjectTask $task, array $data): array
    {
        return DB::transaction(function () use ($task, $data) {
            if (! empty($data['status_id'])) {
                $st = Status::find($data['status_id']);
                $task->status_id = $st->id;
                $task->status = match (strtolower($st->name)) {
                    'to do' => 'todo',
                    'in progress' => 'in_progress',
                    'review' => 'review',
                    'done' => 'done',
                    default => strtolower(str_replace(' ', '_', $st->name))
                };
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
