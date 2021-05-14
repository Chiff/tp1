import { Component, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute } from '@angular/router';
import { NgForm } from '@angular/forms';
import { CustomHttpError, ErrorResponse, TeamDTO } from '../../../models/sp-api';

@Component({
  selector: 'sp-team-detail',
  templateUrl: './team-detail.component.html',
})
export class TeamDetailComponent {
  @ViewChild('ngForm')
  private ngForm: NgForm;

  public team: TeamDTO;

  addTeammate: boolean = false;
  newMail: string = '';
  error: string = '';

  constructor(private http: HttpClient, private route: ActivatedRoute) {
    this.route.params.subscribe((p) => {
      this.getTeamById(p.id);
    });
  }

  private getTeamById(id: string): void {
    this.http.get<TeamDTO>(`api/team/${id}`).subscribe((data) => {
      this.team = data;
    });
  }

  add(): void {
    this.error = null;

    if (this.ngForm.invalid) {
      this.error = 'Vyplňte všetky povinné údaje';
      return;
    }

    this.http.post(`api/team/${this.team.id}/add`, { user_mail: this.newMail }).subscribe({
      next: () => {
        this.newMail = '';
        this.error = '';
        this.addTeammate = false;

        this.getTeamById(this.team.id);
      },
      error: (err: CustomHttpError<ErrorResponse>) => {
        this.error = err.error.error.message;
      },
    });
  }
}